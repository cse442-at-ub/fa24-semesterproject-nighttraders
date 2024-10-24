<?php
include_once('db.php');

function fetchOverviewData($symbol) {
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
    $data = json_decode($overviewResponse, true);
    return $data;
}

function fetchCurrentPrice($symbol) {
    $timeSeriesCh = curl_init();
    $baseUrl = "https://www.alphavantage.co/query";

    $timeSeriesQueryParams = [
        'function' => 'TIME_SERIES_DAILY',
        'symbol' => $symbol,
        'apikey' => API_KEY
    ];

    $timeSeriesQueryString = http_build_query($timeSeriesQueryParams);

    $timeSeriesURL = $baseUrl . "?" . $timeSeriesQueryString;

    curl_setopt($timeSeriesCh, CURLOPT_URL, $timeSeriesURL);
    curl_setopt($timeSeriesCh, CURLOPT_RETURNTRANSFER, 1);

    $timeSeriesResponse = curl_exec($timeSeriesCh);

    curl_close($timeSeriesCh);

    $data = json_decode($timeSeriesResponse, true);
    return $data;
}


function insertOverviewData($data) {
    global $conn;
    
    $sql = "INSERT INTO stockInfo 
            (Symbol, Name, Exchange, Sector, Industry, EPS, LatestQuarter, 52WeekHigh, 
            52WeekLow, AnalystTargetPrice, AnalystRatingStrongBuy, AnalystRatingBuy, AnalystRatingHold, AnalystRatingSell,
            PercentChanceNextWeekIncrease, PercentChanceNextMonthIncrease)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssdsdddiiiidd", $data['Symbol'], $data['Name'], $data['Exchange'],  $data['Sector'], 
                                          $data['Industry'], $data['EPS'], $data['Latest Quarter'], $data['52WeekHigh'],
                                          $data['52WeekLow'], $data['AnalystTargetPrice'], $data['AnalystRatingStrongBuy'], $data['AnalystRatingBuy'],
                                          $data['AnalystRatingHold'], $data['AnalystRatingSell'], $data['PercentChanceNextWeekIncrease'], $data['PercentChanceNextMonthIncrease']);

    $result = $stmt->execute();
    
    if ($result) {
        echo "Overview data inserted successfully\n";
    } else {
        echo "Failed to insert overview data\n";
    }
}

function insertCurrentPrice($data) {
    global $conn;
    
    // Get the latest date from the Time Series
    $latestDate = end(array_keys($data['Time Series (Daily)']));
    
    // Extract the closing price for the latest date
    $closingPrice = $data['Time Series (Daily)'][$latestDate]['4. close'];
    
    // Prepare the SQL statement
    $sql = "INSERT INTO stockInfo 
            (Current Price)
            VALUES (?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("d", floatval($closingPrice));
    
    $result = $stmt->execute();
    
    if ($result) {
        echo "Current Price for $latestDate inserted successfully\n";
    } else {
        echo "Failed to insert current price data\n";
    }
}

$stocksToFetch = array("IBM", "T", "AAPL","GOOG","AMZN","TSLA","NVDA","INTC","DIS","MSFT");

foreach ($stocksToFetch as $symbol) {
    try {
        $overviewData = fetchOverviewData($symbol);
        insertOverviewData($overviewData);
        $currentPrice = fetchCurrentPrice($symbol);
        insertCurrentPrice($overviewData);

    } catch (Exception $e) {
        echo "Error processing $symbol: " . $e->getMessage() . "\n";
    }
}