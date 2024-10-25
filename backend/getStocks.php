<?php
include_once('db.php');

function fetchStock($symbol) {
    $overviewCh = curl_init();

    $baseUrl = "https://www.alphavantage.co/query";
    
    $overviewQueryParams = [
        'function' => 'OVERVIEW',
        'symbol' => $symbol,
        'apikey' => API_KEY
    ];

    // Encode query parameters
    $overviewQueryString = http_build_query($overviewQueryParams);
    
    // Set the full URL with query string
    $overviewURL = $baseUrl . "?" . $overviewQueryString;

    curl_setopt($overviewCh, CURLOPT_URL, $overviewURL);
    curl_setopt($overviewCh, CURLOPT_RETURNTRANSFER, 1);
        
    // Execute the requests
    $overviewResponse = curl_exec($overviewCh);
    
    // Close the cURL sessions
    curl_close($overviewCh);

    // Decode and return the JSON response
    $overviewData = json_decode($overviewResponse, true);

    return $overviewData;
}

function insertStock($overviewData,) {
    global $conn;
    
    $sql = "INSERT INTO stockInfo 
            (Symbol, Name, Exchange, Sector, Industry, EPS, LatestQuarter, 52WeekHigh, 
            52WeekLow, AnalystTargetPrice, AnalystRatingStrongBuy, AnalystRatingBuy, AnalystRatingHold, AnalystRatingSell,
            PercentChanceNextWeekIncrease, PercentChanceNextMonthIncrease)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssdsdddiiiidd", $overviewData['Symbol'], $overviewData['Name'], $overviewData['Exchange'],  $overviewData['Sector'], 
                                          $overviewData['Industry'], $overviewData['EPS'], $overviewData['LatestQuarter'], $overviewData['52WeekHigh'],
                                          $overviewData['52WeekLow'], $overviewData['AnalystTargetPrice'], $overviewData['AnalystRatingStrongBuy'], $overviewData['AnalystRatingBuy'],
                                          $overviewData['AnalystRatingHold'], $overviewData['AnalystRatingSell'], $overviewData['PercentChanceNextWeekIncrease'], 
                                          $overviewData['PercentChanceNextMonthIncrease']);
    $result = $stmt->execute();
    
    if ($result) {
        echo "Stock data inserted successfully\n";
    } else {
        echo "Failed to insert stock data\n";
    }
    $stmt->close();
}

$stocksToFetch = array("IBM", "T", "AAPL","GOOG","AMZN","TSLA","NVDA","INTC","DIS","MSFT");

// fetch and insert the data for 10 preset stocks
foreach ($stocksToFetch as $symbol) {
    try {
        $stockData = fetchStock($symbol);
        insertStock($stockData);
    } catch (Exception $e) {
        echo "Error processing $symbol: " . $e->getMessage() . "\n";
    }
}
?>