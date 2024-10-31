// import React, { useState, useEffect } from "react";
// import { useNavigate } from "react-router-dom";
// import "./Dashboard.css";

// const Dashboard: React.FC = () => {
//   const [name, setName] = useState<string>(""); // State to hold the fetched name
//   const [loading, setLoading] = useState<boolean>(true); // State for loading status
//   const [error, setError] = useState<Error | null>(null); // State to handle errors
//   const navigate = useNavigate();

//   const handleLogout = async () => {
//     try {
//       const response = await fetch(
//         "https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442e/backend/logout.php",
//         {
//           method: "POST",
//           credentials: "include", // Enables sending cookies
//         }
//       );

//       if (response.ok) {
//         console.log("Logged out successfully.");
//       } else {
//         console.error("Logout failed.");
//       }
//     } catch (error) {
//       console.error("Error during logout:", error);
//     }
//     navigate("/"); // Redirect after logout
//   };

//   // Fetch data from dashboard.php when the component mounts
//   useEffect(() => {
//     fetch(
//       "https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442e/backend/dashboard.php",
//       {
//         method: "GET",
//         credentials: "include", // Enables sending cookies
//       }
//     )
//       .then((response) => {
//         if (!response.ok) {
//           throw new Error("Network response was not ok");
//         }
//         return response.json();
//       })
//       .then((data) => {
//         if (data.name) {
//           setName(data.name);
//         } else {
//           setError(new Error(data.error || "Failed to fetch user data"));
//         }
//         setLoading(false);
//       })
//       .catch((error) => {
//         setError(error);
//         setLoading(false);
//       });
//   }, []);

//   if (loading) {
//     return <div className="App">Loading...</div>;
//   }

//   if (error) {
//     return <div className="App">Error: {error.message}</div>;
//   }

//   return (
//     <div className="App">
//       <div className="top-bar">
//         <button className="logo" onClick={handleLogout}>
//           NightTraders
//         </button>
//         <button className="logout-button" onClick={handleLogout}>
//           Logout
//         </button>
//       </div>
//       <div className="content">
//         <h2>Welcome, {name}</h2>
//       </div>
//     </div>
//   );
// };

// export default Dashboard;

import React, { useEffect, useState } from "react";
import axios from "axios";
import { Line } from "react-chartjs-2";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
} from "chart.js";

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
);

const STOCKS = ["AAPL", "MSFT", "GOOGL", "AMZN", "TSLA"]; // Top 5 stocks

// Define types for stock data
interface StockData {
  symbol: string;
  dates: string[];
  prices: number[];
}

const App: React.FC = () => {
  const [chartData, setChartData] = useState<any>(null);
  const [loading, setLoading] = useState<boolean>(true);

  const API_KEY = "B1CHUREPYT8OW7HJ";

  // Fetch stock data from Alpha Vantage API
  const fetchStockData = async () => {
    try {
      const promises = STOCKS.map((stock) =>
        axios.get(
          `https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=${stock}&apikey=${API_KEY}`
        )
      );

      const responses = await Promise.all(promises);

      const stockPrices: StockData[] = responses.map((response) => {
        const dailyData = response.data["Time Series (Daily)"];
        const dates = Object.keys(dailyData).slice(0, 30).reverse();
        const prices = dates.map(
          (date) => parseFloat(dailyData[date]["4. close"])
        );

        const symbol = response.config.url
          ?.split("symbol=")[1]
          .split("&")[0] as string;

        return { symbol, dates, prices };
      });

      const data = {
        labels: stockPrices[0].dates, // Dates from the first stock (same for all)
        datasets: stockPrices.map((stock) => ({
          label: stock.symbol,
          data: stock.prices,
          fill: false,
          borderColor: getRandomColor(),
          tension: 0.1,
        })),
      };

      setChartData(data);
      setLoading(false);
    } catch (error) {
      console.error("Error fetching stock data", error);
    }
  };

  // Generate random colors for chart lines
  const getRandomColor = (): string => {
    const letters = "0123456789ABCDEF";
    let color = "#";
    for (let i = 0; i < 6; i++) {
      color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
  };

  useEffect(() => {
    fetchStockData();
  }, []);

  return (
    <div>
      <h1>Top 5 Stocks Trend</h1>
      {loading ? (
        <p>Loading...</p>
      ) : (
        <Line
          data={chartData}
          options={{
            responsive: true,
            plugins: {
              legend: { position: "top" },
            },
          }}
        />
      )}
    </div>
  );
};

export default App;
