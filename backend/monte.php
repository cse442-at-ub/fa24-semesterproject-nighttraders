<?php
include_once('db.php');
// Define the path to the JSON file

// Read the contents of the JSON file
//$jsonData = file_get_contents($jsonFilePath);
$stmt = $conn-->prepare("SELECT TimeSeries FROM stockInfo WHERE Symbol = :symbol");
$stmt->execute(['symbol' => 'AAPL']);

    // Fetch the result
$jsonData = $stmt->fetch(PDO::FETCH_ASSOC);

// Decode the JSON data into a PHP associative array
$dataArray = json_decode($jsonData, true);

// Initialize a variable to hold the sum of the "open" prices
$sum = 0.0;
$count = 0;
$changes = [];
$opens = [];

function randomNormal($mean = 0, $stdDev = 1) {
    // Generate two independent uniformly distributed random numbers
    $u1 = mt_rand() / mt_getrandmax(); // (0, 1)
    $u2 = mt_rand() / mt_getrandmax(); // (0, 1)

    // Apply the Box-Muller transform
    $z0 = sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);

    // Scale to the desired mean and standard deviation
    return $z0 * $stdDev + $mean;
}
function getWeekdayDates($startDate, $endDate) {
    // Create a DateTime object for the start date
    $start = new DateTime($startDate);
    // Create a DateTime object for the end date
    $end = new DateTime($endDate);
    
    // Ensure the end date is inclusive
    $end->modify('+1 day');
    
    // Array to hold the weekday dates
    $weekdays = [];
    
    // Loop through each date in the range
    for ($date = $start; $date < $end; $date->modify('+1 day')) {
        // Check if the date is a weekday (1 = Monday, 7 = Sunday)
        if ($date->format('N') < 6) { // Weekdays are 1 (Monday) to 5 (Friday)
            $weekdays[] = $date->format('Y-m-d'); // Add the date to the array in 'YYYY-MM-DD' format
        }
    }
    
    return $weekdays;
}

// Example usage
$startDate = '2024-10-25'; // Start date
$endDate = '2025-01-31';   // End date
$weekdayDates = getWeekdayDates($startDate, $endDate);

// Example usage:
$mean = 50;   // Mean of the normal distribution
$stdDev = 10; // Standard deviation of the normal distribution
$number = randomNormal($mean, $stdDev);
echo "Random number from normal distribution: " . $number . "\n";
// Check if the "Time Series (Daily)" key exists in the array
if (isset($dataArray["Time Series (Daily)"])) {
    // Loop through each day's data
    foreach ($dataArray["Time Series (Daily)"] as $date => $dailyData) {
        // Check if the "1. open" key exists
        if (isset($dailyData["1. open"])) {
            if (isset($dailyData["4. close"])) {
            // Add the "open" price to the sum (convert to float)
            $opens[$date] = floatval($dailyData["1. open"]);
            $changes[$date] = floatval($dailyData["4. close"])-floatval($dailyData["1. open"]);
            $count = $count + 1;
            $sum = floatval($sum) + floatval($dailyData["4. close"])-floatval($dailyData["1. open"]);
            }
        }
    }
    $mean = floatval($sum/$count);
    $vartotal = 0.0;
    foreach ($changes as $date => $change){
        $vartotal = $vartotal + ($change - $mean) * ($change - $mean);
    }
    $stdev = sqrt(floatval($vartotal/$count));
    echo "The sum of the open prices is: " . number_format(sqrt($stdev), 2) . "\n";
}
ksort($opens);

// Output the result
echo "The sum of the open prices is: " . $mean . "\n";
$combinedDates = array_unique(array_merge(array_keys($opens), $weekdayDates));
$dates = json_encode(array_keys($combinedDates));
$maindata = [];
$prevval = 0.0;
$maxit = 100;
for ($itnum = 0; $itnum < $maxit; $itnum++) {
    foreach ($combinedDates as $date) { // Loop to create 100 entries
        if (isset($opens[$date])) {
            $maindata[$itnum][$date] = $opens[$date]; // Use opening value
        } else {
            $maindata[$itnum][$date] = $prevval + randomNormal($mean, $stdev); // Generate random value
        }
        $prevval = $maindata[$itnum][$date];
    }
}
// Prepare data for Chart.js
$dates = array_keys($maindata[0]); // Get the date keys from the first entry
$dataSets = [];

// Create datasets for each itnum iteration
for ($itnum = 0; $itnum < $maxit; $itnum++) {
    $dataSets[$itnum] = []; // Initialize array for this dataset
    foreach ($dates as $date) {
        if (isset($maindata[$itnum][$date])) {
            $dataSets[$itnum][] = $maindata[$itnum][$date]; // Collect values for this dataset
        } else {
            $dataSets[$itnum][] = null; // Handle missing values
        }
    }
}

// Convert to JSON for JavaScript
$datesJson = json_encode($dates);
$dataSetsJson = json_encode(array_values($dataSets));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Array Plotting with Chart.js</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<h2>Stock Price Changes Over Time</h2>
<canvas id="myChart" width="400" height="200"></canvas>

<script>
    // Fetch PHP data into JavaScript
    const labels = <?php echo $datesJson; ?>; // Dates as x-axis labels
    const dataSets = <?php echo $dataSetsJson; ?>; // All values for plotting

    // Create the chart
    const ctx = document.getElementById('myChart').getContext('2d');

    // Prepare datasets for Chart.js
    const datasets = dataSets.map((data, index) => ({
        label: `Iteration ${index + 1}`,
        data: data,
        borderColor: `rgba(75, ${index * 2 % 255}, ${192 - index * 2 % 192}, 1)`,
        backgroundColor: `rgba(75, ${index * 2 % 255}, ${192 - index * 2 % 192}, 0.2)`,
        borderWidth: 1
    }));

    const myChart = new Chart(ctx, {
        type: 'line', // Specify the chart type
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>