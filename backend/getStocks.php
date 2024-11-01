<?php
// backend/getStocks.php
include_once('db.php');
include_once('config.php');

/**
 * Fetches stock overview and time series data from Alpha Vantage API.
 *
 * @param string $symbol The stock symbol to fetch.
 * @return array|null Associative array containing stock data or null on failure.
 */
function fetchStock($symbol) {
    $baseUrl = "https://www.alphavantage.co/query";
    
    // Fetch Overview Data
    $overviewQueryParams = [
        'function' => 'OVERVIEW',
        'symbol' => $symbol,
        'apikey' => API_KEY
    ];
    $overviewURL = $baseUrl . "?" . http_build_query($overviewQueryParams);
    
    $overviewCh = curl_init();
    curl_setopt($overviewCh, CURLOPT_URL, $overviewURL);
    curl_setopt($overviewCh, CURLOPT_RETURNTRANSFER, 1);
    $overviewResponse = curl_exec($overviewCh);
    
    if ($overviewResponse === false) {
        error_log("cURL Error (Overview): " . curl_error($overviewCh));
        curl_close($overviewCh);
        return null;
    }
    curl_close($overviewCh);
    
    $overviewData = json_decode($overviewResponse, true);
    if (!$overviewData || empty($overviewData)) {
        error_log("No overview data returned for symbol: $symbol");
        return null;
    }

    // Check for API error messages
    if (isset($overviewData['Note']) || isset($overviewData['Information'])) {
        error_log("API Error for symbol $symbol: " . ($overviewData['Note'] ?? $overviewData['Information']));
        return null;
    }

    // Fetch TimeSeries Data
    $timeSeriesQueryParams = [
        'function' => 'TIME_SERIES_DAILY_ADJUSTED',
        'symbol' => $symbol,
        'apikey' => API_KEY,
        'outputsize' => 'compact'
    ];
    $timeSeriesURL = $baseUrl . "?" . http_build_query($timeSeriesQueryParams);
    
    $timeSeriesCh = curl_init();
    curl_setopt($timeSeriesCh, CURLOPT_URL, $timeSeriesURL);
    curl_setopt($timeSeriesCh, CURLOPT_RETURNTRANSFER, 1);
    $timeSeriesResponse = curl_exec($timeSeriesCh);
    
    if ($timeSeriesResponse === false) {
        error_log("cURL Error (TimeSeries): " . curl_error($timeSeriesCh));
        curl_close($timeSeriesCh);
        return null;
    }
    curl_close($timeSeriesCh);
    
    $timeSeriesData = json_decode($timeSeriesResponse, true);
    if (!$timeSeriesData || empty($timeSeriesData)) {
        error_log("No TimeSeries data returned for symbol: $symbol");
        return null;
    }

    // Check for API error messages
    if (isset($timeSeriesData['Note']) || isset($timeSeriesData['Information'])) {
        error_log("API Error for symbol $symbol: " . ($timeSeriesData['Note'] ?? $timeSeriesData['Information']));
        return null;
    }

    // Combine Overview and TimeSeries Data
    $overviewData['TimeSeries'] = $timeSeriesData;

    return $overviewData;
}

/**
 * Inserts stock data into the database.
 *
 * @param array $overviewData The stock data to insert.
 * @return void
 */
function insertStock($overviewData) {
    global $conn;
    
    // Define required fields
    $requiredFields = [
        'Symbol', 'Name', 'Exchange', 'Sector', 'Industry',
        'EPS', 'LatestQuarter', '52WeekHigh', '52WeekLow',
        'AnalystTargetPrice', 'TimeSeries'
    ];

    // Check for missing fields
    foreach ($requiredFields as $field) {
        if (!isset($overviewData[$field])) {
            error_log("Missing field: $field in symbol: " . ($overviewData['Symbol'] ?? 'Unknown'));
            return;
        }
    }

    // Prepare SQL statement
    $sql = "INSERT INTO stockInfo (
                Symbol, Name, Exchange, Sector, Industry,
                EPS, LatestQuarter, `52WeekHigh`, `52WeekLow`,
                AnalystTargetPrice,
                TimeSeries
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        return;
    }

    // Encode TimeSeries as JSON
    $timeSeriesJson = json_encode($overviewData['TimeSeries']);

    // Bind parameters
    $stmt->bind_param(
        "sssssdsddds",
        $overviewData['Symbol'],
        $overviewData['Name'],
        $overviewData['Exchange'],
        $overviewData['Sector'],
        $overviewData['Industry'],
        floatval($overviewData['EPS']),
        $overviewData['LatestQuarter'],
        floatval($overviewData['52WeekHigh']),
        floatval($overviewData['52WeekLow']),
        floatval($overviewData['AnalystTargetPrice']),
        $timeSeriesJson
    );

    // Execute and log
    if ($stmt->execute()) {
        echo "Stock data for {$overviewData['Symbol']} inserted successfully.\n";
    } else {
        error_log("Failed to insert stock data for {$overviewData['Symbol']}: " . $stmt->error);
    }

    $stmt->close();
}

// List of stock symbols to fetch
$stocksToFetch = ["IBM", "T", "AAPL", "GOOG", "AMZN", "TSLA", "NVDA", "INTC", "DIS", "MSFT"];

// Fetch and insert each stock
foreach ($stocksToFetch as $symbol) {
    try {
        $stockData = fetchStock($symbol);
        if ($stockData) {
            insertStock($stockData);
        }
    } catch (Exception $e) {
        error_log("Error processing $symbol: " . $e->getMessage());
    }
}
?>
