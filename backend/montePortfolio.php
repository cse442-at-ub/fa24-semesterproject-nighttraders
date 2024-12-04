<?php
// Start session to check if user is logged in
session_start();


include_once('config.php');  // Configuration settings
include_once('db.php');       // Database connection
include_once('getStocks.php'); // Include getStocks functions

// Set response headers
header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Credentials: true');
header("Content-Type: application/json");


if (!isset($_SESSION["user"])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

// Function to fetch stock data from the database
function getStockData($symbol) {
    global $conn;
    
    // Prepare statement to fetch stock data
    $stmt = $conn->prepare("SELECT * FROM stockInfo WHERE Symbol = ?");
    if (!$stmt) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        return null;
    }
    $stmt->bind_param("s", $symbol);
    $stmt->execute();
    $result = $stmt->get_result();
    $stockData = $result->fetch_assoc();
    $stmt->close();

    // If stock data not found, fetch and insert
    if (!$stockData) {
        $overviewData = fetchStock($symbol);
        if ($overviewData) {
            insertStock($overviewData);
            // Fetch the data again after insertion
            $stmt = $conn->prepare("SELECT * FROM stockInfo WHERE Symbol = ?");
            $stmt->bind_param("s", $symbol);
            $stmt->execute();
            $result = $stmt->get_result();
            $stockData = $result->fetch_assoc();
            $stmt->close();
        } else {
            error_log("Failed to fetch and insert stock data for symbol: $symbol");
            return null;
        }
    }

    return $stockData;
}

// Function to perform Monte Carlo simulation
function calculateMonteCarlo($iterations = 1000, $days = 252, $ownedStocks) {

    // Initialize arrays for mean, standard deviation, and current prices
    $means = [];
    $stdDevs = [];
    $currentPrices = [];

    // Loop through each stock in the portfolio
    foreach ($ownedStocks as $stock) {
        $symbol = $stock['symbol'];

        // Fetch stock data
        $stockData = getStockData($symbol);

        if (!$stockData) {
            error_log("Stock data for $symbol not found.");
            continue;  // Skip if stock data is not found
        }

        // Decode and extract the closing prices
        $prices = array_values(json_decode($stockData['TimeSeries'], true)['Time Series (Daily)']);
        $prices = array_reverse($prices)
        $closingPrices = [];

        foreach ($prices as $dayData) {
            $closingPrices[] = floatval($dayData['4. close']);
        }

        // Calculate daily returns
        $returns = [];
        for ($i = 1; $i < count($closingPrices); $i++) {
            $returns[] = ($closingPrices[$i] - $closingPrices[$i - 1]) / $closingPrices[$i - 1];
        }

        // Calculate mean and standard deviation for the stock
        $mean = array_sum($returns) / count($returns);
        $variance = 0.0;
        foreach ($returns as $r) {
            $variance += pow($r - $mean, 2);
        }
        $stdDev = sqrt($variance / count($returns));

        // Store the values for each stock
        $means[$symbol] = $mean;
        $stdDevs[$symbol] = $stdDev;
        $currentPrices[$symbol] = end($closingPrices);
    }

    // Run the Monte Carlo simulation
    $portfolioPaths = [];
    for ($i = 0; $i < $iterations; $i++) {
        $portfolioPath = [];
        $portfolioValue = 0;
        $stockprices = [];

        foreach ($ownedStocks as $stock) {
            $symbol = $stock['symbol'];
            $stockprices[$symbol] = $currentPrices[$symbol];
        }

        // Loop through each day of the simulation
        for ($d = 0; $d < $days; $d++) {
            $dayValue = 0;

            // Simulate each stock's value
            foreach ($ownedStocks as $stock) {
                $symbol = $stock['symbol'];
                if (isset($means[$symbol], $stdDevs[$symbol], $currentPrices[$symbol])) {
                    $price = $stockprices[$symbol];
                    $mean = $means[$symbol];
                    $stdDev = $stdDevs[$symbol];

                    // Simulate future stock price
                    $randomReturn = generateRandomNormal($mean, $stdDev);
                    $price *= (1 + $randomReturn);
                    $stockprices[$symbol] = $price;
                    // Update portfolio value
                    $dayValue += $stockprices[$symbol] * $stock['quantity'];
                }
            }

            // Store the portfolio value for this day
            $portfolioValue = $dayValue;
            $portfolioPath[] = $portfolioValue;
        }

        // Store the full path for this iteration
        $portfolioPaths[] = $portfolioPath;
    }

    // Sort paths by the final portfolio value (last value in each path)
    usort($portfolioPaths, function($a, $b) {
        return end($a) <=> end($b);
    });

    // Return the worst, best, and median cases
    $worstCasePath = $portfolioPaths[0];
    $bestCasePath = $portfolioPaths[count($portfolioPaths) - 1];
    $medianCasePath = $portfolioPaths[(int)(count($portfolioPaths) / 2)];

    return [
        'worstCase' => $worstCasePath,
        'bestCase' => $bestCasePath,
        'medianCase' => $medianCasePath
    ];
}

// Function to generate a random normal distribution
function generateRandomNormal($mean, $stdDev) {
    $u = mt_rand() / mt_getrandmax();
    $v = mt_rand() / mt_getrandmax();
    $z = sqrt(-2 * log($u)) * cos(2 * M_PI * $v);
    return $mean + $stdDev * $z;
}

// Handle POST requests for portfolio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user ID from session
    $username = $_SESSION['user'];

    // Fetch portfolio data
    $stmt = $conn->prepare("SELECT OwnedStocks FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // If no portfolio data found
    if (!$user || !$user['OwnedStocks']) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'No portfolio data found for user']);
        exit;
    }

    // Decode the OwnedStocks data
    $ownedStocks = json_decode($user['OwnedStocks'], true);

    // Run Monte Carlo simulation
    $monteCarloResults = calculateMonteCarlo(1000, 30, $ownedStocks);

    // Handle simulation errors
    if (!$monteCarloResults) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Monte Carlo simulation failed']);
        exit;
    }

    // Return the results
    echo json_encode([
        'monteCarloResults' => $monteCarloResults
    ]);
}
?>
