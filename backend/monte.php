<?php
// Start session to check if user is logged in
session_start();


// backend/monte.php
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

/**
 * Retrieves stock data from the database. If not found, fetches from API and inserts into DB.
 *
 * @param string $symbol The stock symbol to retrieve.
 * @return array|null The stock data or null if not found/fetched.
 */
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
            if (!$stmt) {
                error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                return null;
            }
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

/**
 * Performs the Monte Carlo simulation on the provided time series data.
 *
 * @param array $timeSeries The historical stock prices.
 * @param int $iterations Number of simulation iterations.
 * @param int $days Number of days to simulate.
 * @return array The results of the Monte Carlo simulation.
 */
function calculateMonteCarlo($timeSeries, $iterations = 1000, $days = 30) {
    if (!isset($timeSeries['Time Series (Daily)'])) {
        error_log("Time Series data is missing or invalid.");
        return null;
    }

    $prices = array_values($timeSeries['Time Series (Daily)']);
    $prices = array_reverse($prices);
    $closingPrices = [];
    
    foreach ($prices as $dayData) {
        $closingPrices[] = floatval($dayData['4. close']);
    }

    // Calculate daily returns
    $returns = [];
    for ($i = 1; $i < count($closingPrices); $i++) {
        $returns[] = ($closingPrices[$i] - $closingPrices[$i-1]) / $closingPrices[$i-1];
    }

    // Calculate mean and standard deviation
    $mean = array_sum($returns) / count($returns);
    $variance = 0.0;
    foreach ($returns as $r) {
        $variance += pow($r - $mean, 2);
    }
    $stdDev = sqrt($variance / count($returns));

    $currentPrice = end($closingPrices);
    $scenarios = [];

    // Monte Carlo Simulation
    for ($i = 0; $i < $iterations; $i++) {
        $price = $currentPrice;
        $path = [$price];
        for ($d = 0; $d < $days; $d++) {
            $randomReturn = generateRandomNormal($mean, $stdDev);
            $price *= (1 + $randomReturn);
            $path[] = $price;
        }
        $scenarios[] = $path;
    }

    // Sort scenarios by their final price
    usort($scenarios, function($a, $b) {
        return end($a) <=> end($b);
    });

    // Extract best case, worst case, and median case
    $worstCase = $scenarios[0];                             // Lowest final price
    $bestCase = $scenarios[count($scenarios) - 1];           // Highest final price
    $medianCase = $scenarios[(int) (count($scenarios) / 2)]; // Median final price
    $evaluation = '';

    if ($finalValues[$bestIndex] > $currentPrice) {
        $evaluation .= "The stock performed exceptionally well, with the best case showing a positive growth above the mean.\n";
    } else {
        $evaluation .= "The stock showed moderate growth in the best case scenario, but it didn't outperform the mean.\n";
    }

    if ($finalValues[$worstIndex] < $currentPrice) {
        $evaluation .= "The worst case reveals that the stock could significantly drop below the mean, showing potential risks.\n";
    } else {
        $evaluation .= "The stock showed some stability even in the worst case, not dipping too far below the mean.\n";
    }

    if ($finalValues[$medianIndex] > $currentPrice) {
        $evaluation .= "The median case indicates that the stock generally performs above the average, showing positive potential.\n";
    } else {
        $evaluation .= "The median case shows that the stock's performance is around the mean, suggesting a stable outlook.\n";
    }
    $evaluation .= "\nOverall Evaluation: ";
    if ($finalValues[$bestIndex] > $finalValues[$worstIndex]) {
        $evaluation .= "The stock shows potential for positive growth, though it is not without risk.\n";
    } else {
        $evaluation .= "The stock presents potential risk, and careful monitoring is needed.\n";
    }
    return [
        'worst' => end($worstCase),
        'average' => array_sum(array_map('end', $scenarios)) / count($scenarios),
        'best' => end($bestCase),
        'scenarios' => [
            'worstCase' => $worstCase,
            'medianCase' => $medianCase,
            'bestCase' => $bestCase
        ]
        'evaluation' => $evaluation
    ];
}

/**
 * Generates a random number following a normal distribution using Box-Muller transform.
 *
 * @param float $mean The mean of the distribution.
 * @param float $stdDev The standard deviation of the distribution.
 * @return float A random number from the normal distribution.
 */
function generateRandomNormal($mean, $stdDev) {
    $u = mt_rand() / mt_getrandmax();
    $v = mt_rand() / mt_getrandmax();
    $z = sqrt(-2 * log($u)) * cos(2 * M_PI * $v);
    return $mean + $stdDev * $z;
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $symbol = $_GET['symbol'] ?? null;
    
    if (!$symbol) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Symbol is required']);
        exit;
    }

    $stockData = getStockData($symbol);

    if (!$stockData) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Stock not found and could not be fetched']);
        exit;
    }

    $timeSeries = json_decode($stockData['TimeSeries'], true);
    
    if (!$timeSeries) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'TimeSeries data is missing']);
        exit;
    }

    $monteCarloResults = calculateMonteCarlo($timeSeries);

    if (!$monteCarloResults) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Monte Carlo simulation failed']);
        exit;
    }

    echo json_encode([
        'stockInfo' => $stockData,
        'monteCarloResults' => $monteCarloResults
    ]);
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $symbol = $data['symbol'] ?? null;
    
    if (!$symbol) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Symbol is required']);
        exit;
    }

    $stockData = getStockData($symbol);

    if (!$stockData) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Stock not found and could not be fetched']);
        exit;
    }

    $timeSeries = json_decode($stockData['TimeSeries'], true);
    
    if (!$timeSeries) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'TimeSeries data is missing']);
        exit;
    }

    $monteCarloResults = calculateMonteCarlo($timeSeries);

    if (!$monteCarloResults) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Monte Carlo simulation failed']);
        exit;
    }

    echo json_encode([
        'monteCarloResults' => $monteCarloResults
    ]);
}
?>