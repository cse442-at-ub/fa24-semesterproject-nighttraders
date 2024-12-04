// src/pages/StockDetails.tsx
import React, { useState, useEffect } from "react";
import { config } from "../config";
import { useParams, useNavigate } from "react-router-dom";
import {
  Box,
  Typography,
  Button,
  CircularProgress,
} from "@mui/material";
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
  ChartData,
} from "chart.js";
import AddCircleOutlineIcon from "@mui/icons-material/AddCircleOutline";
import AddCircleIcon from "@mui/icons-material/AddCircle";

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
);

const StockDetails: React.FC = () => {
  const { symbol } = useParams<{ symbol: string }>();
  const [stockInfo, setStockInfo] = useState<any>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string>("");
  const [chartData, setChartData] = useState<any>(null);
  const [monteCarloData, setMonteCarloData] = useState<any>(null);
  const [isOwned, setIsOwned] = useState<boolean>(false);

  const navigate = useNavigate();

  useEffect(() => {
    const fetchStockDetails = async () => {
      try {
        const response = await fetch(
          `${config.backendUrl}/getStockDetails.php?symbol=${symbol}`,
          {
            credentials: "include",
          }
        );
        const data = await response.json();
        if (data.error) {
          setError(data.error);
        } else {
          setStockInfo(data.stockInfo);
          // Prepare chart data
          const timeSeries = data.stockInfo.TimeSeries["Time Series (Daily)"];
          const labels = Object.keys(timeSeries).reverse();
          const prices = labels.map((date) =>
            parseFloat(timeSeries[date]["4. close"])
          );
          setChartData({
            labels,
            datasets: [
              {
                label: `${symbol} Closing Prices`,
                data: prices,
                borderColor: "rgba(75,192,192,1)",
                fill: false,
              },
            ],
          });
        }
      } catch (err) {
        console.error(err);
        setError("Failed to fetch stock details.");
      } finally {
        setLoading(false);
      }
    };
    fetchStockDetails();
  }, [symbol]);

  useEffect(() => {
    const checkIfOwned = async () => {
      try {
        const response = await fetch(
          `${config.backendUrl}/getOwnedStocks.php`,
          {
            credentials: "include",
          }
        );
        const data = await response.json();
        if (data.error) {
          console.error(data.error);
        } else {
          // Check if the stock is owned (quantity > 0) or bookmarked (quantity = 0)
          const ownedStock = data.OwnedStocks.find(
            (stock: any) => stock.symbol === symbol
          );
          if (ownedStock) {
            setIsOwned(ownedStock.quantity > 0);
          } else {
            setIsOwned(false);
          }
        }
      } catch (err) {
        console.error("Failed to fetch owned stocks", err);
      }
    };
    checkIfOwned();
  }, [symbol]);

  const handleRunMonteCarlo = async () => {
    // Fetch Monte Carlo data for the individual stock
    try {
      const response = await fetch(`${config.backendUrl}/monte.php`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ symbol }),
      });
      const data = await response.json();
      if (data.error) {
        setError(data.error);
      } else {
        setMonteCarloData(data.monteCarloResults);
      }
    } catch (err) {
      console.error(err);
      setError("Failed to run Monte Carlo simulation.");
    }
  };

  const handleToggleOwned = async () => {
    // Toggle between adding and removing the stock from the portfolio
    const newQuantity = isOwned ? 0 : 1; // Set to 1 when bookmarking

    try {
      const priceResponse = await fetch(
        `${config.backendUrl}/getStockPrice.php?symbol=${symbol}`
      );
      const priceData = await priceResponse.json();

      const response = await fetch(
        `${config.backendUrl}/updateOwnedStocks.php`,
        {
          method: "POST",
          credentials: "include",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            symbol: symbol,
            quantity: newQuantity,
            price: priceData.price,
          }),
        }
      );

      const data = await response.json();
      if (data.error) {
        console.error(data.error);
      } else {
        setIsOwned(!isOwned);
      }
    } catch (err) {
      console.error(
        "Failed to update owned stocks or retrieve stock price",
        err
      );
    }
  };

  return (
    <div
      style={{ backgroundColor: "#252525", minHeight: "100vh", color: "white" }}
    >
      <div className="top-bar">
        <button className="logo" onClick={() => navigate("/dashboard")}>
          NightTraders
        </button>
        <div>
          <button
            className="portfolio-button"
            onClick={() => navigate("/portfolio")}
          >
            PORTFOLIO
          </button>
          <button className="logout-button" onClick={() => navigate("/")}>
            LOGOUT
          </button>
        </div>
      </div>
      <Box sx={{ p: 3 }}>
        {loading ? (
          <Box
            display="flex"
            justifyContent="center"
            alignItems="center"
            minHeight="200px"
          >
            <CircularProgress />
          </Box>
        ) : error ? (
          <Typography variant="h6" color="error" align="center">
            {error}
          </Typography>
        ) : (
          <>
            <Box
              sx={{ display: "flex", alignItems: "center", flexWrap: "wrap" }}
            >
              <Typography variant="h4" sx={{ mr: 2 }}>
                {stockInfo.Name} ({stockInfo.Symbol})
              </Typography>
              {/* Updated Button with Icon and Text */}
              <Button
                onClick={handleToggleOwned}
                variant="contained"
                color={isOwned ? "success" : "primary"}
                startIcon={isOwned ? <AddCircleIcon /> : <AddCircleOutlineIcon />}
                sx={{ ml: 2 }}
              >
                {isOwned ? "Owned" : "Add to Portfolio"}
              </Button>
            </Box>
            {chartData && (
              <Box
                sx={{
                  backgroundColor: "white",
                  p: 2,
                  borderRadius: 2,
                  mt: 2,
                  width: "95%",
                  maxWidth: "1400px",
                  height: { xs: 300, sm: 400 },
                  mx: "auto",
                }}
              >
                <Line
                  data={chartData}
                  options={{
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                      legend: {
                        position: "top" as const,
                      },
                      title: {
                        display: true,
                        text: "Stock Closing Prices",
                      },
                    },
                    scales: {
                      y: {
                        beginAtZero: false,
                      },
                    },
                  }}
                />
              </Box>
            )}
            <Button
              variant="contained"
              sx={{ mt: 2 }}
              onClick={handleRunMonteCarlo}
            >
              Run Monte-Carlo Simulation
            </Button>
            {monteCarloData &&
            monteCarloData.scenarios &&
            monteCarloData.scenarios.worstCase &&
            Array.isArray(monteCarloData.scenarios.worstCase) ? (
              <Box
                sx={{
                  backgroundColor: "white",
                  p: 2,
                  borderRadius: 2,
                  mt: 2,
                  maxWidth: "1400px",
                  width: "95%",
                  height: { xs: 300, sm: 400 },
                  mx: "auto",
                }}
              >
                <Line
                  data={{
                    labels: Array.from(
                      { length: monteCarloData.scenarios.worstCase.length },
                      (_, i) => `Day ${i + 1}`
                    ),
                    datasets: [
                      {
                        label: "Worst Case",
                        data: monteCarloData.scenarios.worstCase,
                        borderColor: "red",
                        fill: false,
                        borderWidth: 1,
                      },
                      {
                        label: "Median Case",
                        data: monteCarloData.scenarios.medianCase,
                        borderColor: "gray",
                        fill: false,
                        borderWidth: 1,
                      },
                      {
                        label: "Best Case",
                        data: monteCarloData.scenarios.bestCase,
                        borderColor: "green",
                        fill: false,
                        borderWidth: 1,
                      },
                    ],
                  }}
                  options={{
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                      legend: {
                        position: "top" as const,
                      },
                      title: {
                        display: true,
                        text: "Monte-Carlo Simulation",
                      },
                    },
                    scales: {
                      y: {
                        beginAtZero: false,
                      },
                    },
                  }}
                />
              </Box>
            ) : monteCarloData ? (
              <Typography variant="body1" color="error" sx={{ mt: 2 }}>
                Monte Carlo data is unavailable.
              </Typography>
            ) : null}
            <Box sx={{ mt: 2 }}>
              <Typography variant="h6">Stock Information</Typography>
              <Typography>Symbol: {stockInfo.Symbol}</Typography>
              <Typography>Name: {stockInfo.Name}</Typography>
              <Typography>Exchange: {stockInfo.Exchange}</Typography>
              <Typography>Sector: {stockInfo.Sector}</Typography>
              <Typography>Industry: {stockInfo.Industry}</Typography>
              <Typography>EPS: {stockInfo.EPS}</Typography>
              <Typography>Latest Quarter: {stockInfo.LatestQuarter}</Typography>
              <Typography>52 Week High: {stockInfo["52WeekHigh"]}</Typography>
              <Typography>52 Week Low: {stockInfo["52WeekLow"]}</Typography>
              <Typography>
                Analyst Target Price: {stockInfo.AnalystTargetPrice}
              </Typography>
            </Box>
          </>
        )}
      </Box>
    </div>
  );
};

export default StockDetails;
