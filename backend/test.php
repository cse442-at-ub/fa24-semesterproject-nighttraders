<?php
// backend/test.php

/**
 * This script populates the `stockInfo` table with pseudo-data.
 * It generates mock TimeSeries data for a predefined list of stock symbols.
 * Run this script once to seed your database.
 */

// Enable error reporting for debugging (remove or comment out in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to a file instead of displaying them (for production environments)
// You can comment these lines out if you prefer to see errors directly
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// Include database configuration and connection
include_once('db.php');
include_once('config.php');

/**
 * Generates mock TimeSeries data for the past $days days, excluding weekends.
 *
 * @param int $days Number of past days to generate data for.
 * @param float $startPrice Starting price for the stock.
 * @return array Mock TimeSeries data.
 */
function generateMockTimeSeries($days = 30, $startPrice = 100.0) {
    $timeSeries = [];
    $currentDate = new DateTime();
    $currentPrice = $startPrice;

    // Define the number of trading days (exclude weekends)
    $tradingDays = 0;
    while ($tradingDays < $days) {
        // Skip weekends
        if ($currentDate->format('N') < 6) { // 6 and 7 are Saturday and Sunday
            $dateStr = $currentDate->format('Y-m-d');

            // Generate random daily return between -2% to +2%
            $dailyReturn = rand(-200, 200) / 10000; // -0.02 to +0.02
            $open = round($currentPrice, 2);
            $close = round($open * (1 + $dailyReturn), 2);
            $high = round(max($open, $close) * (1 + rand(0, 100) / 10000), 2); // Up to +1% higher
            $low = round(min($open, $close) * (1 - rand(0, 100) / 10000), 2); // Up to -1% lower
            $volume = rand(1000000, 5000000); // Random volume between 1M and 5M

            $timeSeries[$dateStr] = [
                '1. open' => number_format($open, 2, '.', ''),
                '2. high' => number_format($high, 2, '.', ''),
                '3. low' => number_format($low, 2, '.', ''),
                '4. close' => number_format($close, 2, '.', ''),
                '5. adjusted close' => number_format($close, 2, '.', ''),
                '6. volume' => (string)$volume
            ];

            $currentPrice = $close; // Update current price for next day
            $tradingDays++;
        }
        // Move to the previous day
        $currentDate->modify('-1 day');
    }

    return ['Time Series (Daily)' => $timeSeries];
}

// Define a list of stocks to insert
$stocks = [
    [
        'Symbol' => 'IBM',
        'Name' => 'International Business Machines',
        'Exchange' => 'NYSE',
        'Sector' => 'Technology',
        'Industry' => 'Computer & Office Equipment',
        'EPS' => 9.08,
        'LatestQuarter' => '2024-06-30', // Correct format
        '52WeekHigh' => 237.37,
        '52WeekLow' => 136.30,
        'AnalystTargetPrice' => 212.63,
        'StartPrice' => 150.00
    ],
    [
        'Symbol' => 'AAPL',
        'Name' => 'Apple Inc.',
        'Exchange' => 'NASDAQ',
        'Sector' => 'Technology',
        'Industry' => 'Consumer Electronics',
        'EPS' => 5.11,
        'LatestQuarter' => '2024-06-30', // Correct format
        '52WeekHigh' => 180.00,
        '52WeekLow' => 120.00,
        'AnalystTargetPrice' => 165.00,
        'StartPrice' => 145.00
    ],
    [
        'Symbol' => 'GOOG',
        'Name' => 'Alphabet Inc.',
        'Exchange' => 'NASDAQ',
        'Sector' => 'Communication Services',
        'Industry' => 'Internet Content & Information',
        'EPS' => 6.50,
        'LatestQuarter' => '2024-06-30', // Correct format
        '52WeekHigh' => 2800.00,
        '52WeekLow' => 2200.00,
        'AnalystTargetPrice' => 2500.00,
        'StartPrice' => 2700.00
    ],
    [
        'Symbol' => 'AMZN',
        'Name' => 'Amazon.com, Inc.',
        'Exchange' => 'NASDAQ',
        'Sector' => 'Consumer Cyclical',
        'Industry' => 'Internet Retail',
        'EPS' => 42.51,
        'LatestQuarter' => '2024-06-30', // Correct format
        '52WeekHigh' => 3500.00,
        '52WeekLow' => 2900.00,
        'AnalystTargetPrice' => 3200.00,
        'StartPrice' => 3300.00
    ],
    [
        'Symbol' => 'TSLA',
        'Name' => 'Tesla, Inc.',
        'Exchange' => 'NASDAQ',
        'Sector' => 'Consumer Cyclical',
        'Industry' => 'Auto Manufacturers',
        'EPS' => 3.56,
        'LatestQuarter' => '2024-06-30', // Correct format
        '52WeekHigh' => 1200.00,
        '52WeekLow' => 650.00,
        'AnalystTargetPrice' => 900.00,
        'StartPrice' => 800.00
    ],
    [
        'Symbol' => 'NVDA',
        'Name' => 'NVIDIA Corporation',
        'Exchange' => 'NASDAQ',
        'Sector' => 'Technology',
        'Industry' => 'Semiconductors',
        'EPS' => 9.00,
        'LatestQuarter' => '2024-06-30', // Correct format
        '52WeekHigh' => 300.00,
        '52WeekLow' => 150.00,
        'AnalystTargetPrice' => 250.00,
        'StartPrice' => 200.00
    ],
    [
        'Symbol' => 'INTC',
        'Name' => 'Intel Corporation',
        'Exchange' => 'NASDAQ',
        'Sector' => 'Technology',
        'Industry' => 'Semiconductors',
        'EPS' => 4.50,
        'LatestQuarter' => '2024-06-30', // Correct format
        '52WeekHigh' => 75.00,
        '52WeekLow' => 45.00,
        'AnalystTargetPrice' => 65.00,
        'StartPrice' => 60.00
    ],
    [
        'Symbol' => 'DIS',
        'Name' => 'The Walt Disney Company',
        'Exchange' => 'NYSE',
        'Sector' => 'Communication Services',
        'Industry' => 'Entertainment',
        'EPS' => 3.40,
        'LatestQuarter' => '2024-06-30', // Correct format
        '52WeekHigh' => 200.00,
        '52WeekLow' => 150.00,
        'AnalystTargetPrice' => 180.00,
        'StartPrice' => 170.00
    ],
    [
        'Symbol' => 'MSFT',
        'Name' => 'Microsoft Corporation',
        'Exchange' => 'NASDAQ',
        'Sector' => 'Technology',
        'Industry' => 'Software—Infrastructure',
        'EPS' => 8.05,
        'LatestQuarter' => '2024-06-30', // Correct format
        '52WeekHigh' => 350.00,
        '52WeekLow' => 250.00,
        'AnalystTargetPrice' => 330.00,
        'StartPrice' => 320.00
    ],
    [
        'Symbol' => 'T',
        'Name' => 'AT&T Inc.',
        'Exchange' => 'NYSE',
        'Sector' => 'Telecom',
        'Industry' => 'Telecom Services',
        'EPS' => 2.10,
        'LatestQuarter' => '2024-06-30', // Correct format
        '52WeekHigh' => 35.00,
        '52WeekLow' => 25.00,
        'AnalystTargetPrice' => 30.00,
        'StartPrice' => 28.00
    ],
];

// Prepare the SQL statement with placeholders
$sql = "INSERT INTO stockInfo (
            Symbol, Name, Exchange, Sector, Industry,
            EPS, LatestQuarter, `52WeekHigh`, `52WeekLow`,
            AnalystTargetPrice, TimeSeries
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            Name = VALUES(Name),
            Exchange = VALUES(Exchange),
            Sector = VALUES(Sector),
            Industry = VALUES(Industry),
            EPS = VALUES(EPS),
            LatestQuarter = VALUES(LatestQuarter),
            `52WeekHigh` = VALUES(`52WeekHigh`),
            `52WeekLow` = VALUES(`52WeekLow`),
            AnalystTargetPrice = VALUES(AnalystTargetPrice),
            TimeSeries = VALUES(TimeSeries)";

// Prepare the statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(['error' => "Prepare failed: (" . $conn->errno . ") " . $conn->error]));
}

// Bind parameters: 11 parameters
// "sssssdsddds" corresponds to:
// s - Symbol
// s - Name
// s - Exchange
// s - Sector
// s - Industry
// d - EPS
// s - LatestQuarter
// d - 52WeekHigh
// d - 52WeekLow
// d - AnalystTargetPrice
// s - TimeSeries
$stmt->bind_param(
    "sssssdsddds",
    $symbol,
    $name,
    $exchange,
    $sector,
    $industry,
    $eps,
    $latestQuarter,        // 's' for string (DATE)
    $week52High,
    $week52Low,
    $analystTargetPrice,
    $timeSeriesJson
);

$inserted = 0;
$updated = 0;

// Iterate over each stock and insert/update
foreach ($stocks as $stock) {
    $symbol = $stock['Symbol'];
    $name = $stock['Name'];
    $exchange = $stock['Exchange'];
    $sector = $stock['Sector'];
    $industry = $stock['Industry'];
    $eps = $stock['EPS'];
    $latestQuarter = $stock['LatestQuarter'];
    $week52High = $stock['52WeekHigh'];
    $week52Low = $stock['52WeekLow'];
    $analystTargetPrice = $stock['AnalystTargetPrice'];
    $startPrice = $stock['StartPrice'];

    // Generate mock TimeSeries data
    $mockTimeSeries = generateMockTimeSeries(30, $startPrice);
    $timeSeriesJson = json_encode($mockTimeSeries);

    // Debugging: Print out the values being bound (optional, remove in production)
    echo "Preparing to execute statement for stock: $symbol\n";
    echo "Symbol: $symbol\n";
    echo "Name: $name\n";
    echo "Exchange: $exchange\n";
    echo "Sector: $sector\n";
    echo "Industry: $industry\n";
    echo "EPS: $eps\n";
    echo "LatestQuarter: $latestQuarter\n"; // Should be 'YYYY-MM-DD'
    echo "52WeekHigh: $week52High\n";
    echo "52WeekLow: $week52Low\n";
    echo "AnalystTargetPrice: $analystTargetPrice\n";
    echo "TimeSeries: " . substr($timeSeriesJson, 0, 100) . "...\n"; // Truncate for readability

    // Execute the statement
    if ($stmt->execute()) {
        if ($stmt->affected_rows === 1) {
            $inserted++;
            echo "Inserted stock: $symbol\n\n";
        } elseif ($stmt->affected_rows === 2) {
            // When using ON DUPLICATE KEY UPDATE, affected_rows = 2 means an update
            $updated++;
            echo "Updated stock: $symbol\n\n";
        } else {
            // No change
            echo "No change for stock: $symbol\n\n";
        }
    } else {
        echo "Failed to insert/update stock: $symbol. Error: " . $stmt->error . "\n\n";
    }
}

$stmt->close();
$conn->close();

echo "\nSummary:\nInserted: $inserted\nUpdated: $updated\n";
?>

