// src/pages/Portfolio.tsx
import React, { useState, useEffect } from 'react';
import { config } from '../config';
import {
  Box,
  Typography,
  TextField,
  Button,
  Card,
  CardContent,
  Grid
} from '@mui/material';
import { useNavigate } from 'react-router-dom';
import { Line } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js';
import './Portfolio.css';

interface OwnedStock {
  symbol: string;
  quantity: number;
  price: number;
}

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
);

const Portfolio: React.FC = () => {
  const [ownedStocks, setOwnedStocks] = useState<OwnedStock[]>([]);
  const [quantities, setQuantities] = useState<{ [key: string]: number }>({});
  const [showMonteCarloButton, setShowMonteCarloButton] = useState<boolean>(false);
  const [monteCarloData, setMonteCarloData] = useState<any>(null);
  const navigate = useNavigate();

  useEffect(() => {
    // Fetch owned stocks from the backend
    const fetchOwnedStocks = async () => {
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
          const stocks = data.OwnedStocks; // Should be an array of { symbol, quantity, price }
          setOwnedStocks(stocks);

          // Initialize quantities
          const initialQuantities: { [key: string]: number } = {};
          stocks.forEach((stock: OwnedStock) => {
            initialQuantities[stock.symbol] = stock.quantity;
          });
          setQuantities(initialQuantities);
        }
      } catch (err) {
        console.error("Failed to fetch owned stocks", err);
      }
    };

    fetchOwnedStocks();
  }, []);

  useEffect(() => {
    // Check if any stock has a quantity greater than zero
    const hasQuantities = ownedStocks.some((stock) => stock.quantity > 0);
    setShowMonteCarloButton(hasQuantities);
  }, [ownedStocks]);

  const handleQuantityChange = (symbol: string, value: string) => {
    const qty = parseInt(value) || 0;
    setQuantities((prev) => ({
      ...prev,
      [symbol]: qty,
    }));
  };

  const handleSave = async (symbol: string) => {
    // Update the stock's quantity in ownedStocks
    setOwnedStocks((prev) =>
      prev.map((stock) =>
        stock.symbol === symbol
          ? { ...stock, quantity: quantities[symbol] || 0 }
          : stock
      )
    );

    // Save the quantity to the backend
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
            quantity: quantities[symbol] || 0,
            price: priceData.price,
          }),
        }
      );

      const data = await response.json();
      if (data.error) {
        console.error(data.error);
      } else {
        console.log('Quantity updated successfully');
      }
    } catch (err) {
      console.error('Failed to update stock quantity', err);
    }
  };

  const handleRunMonteCarlo = async () => {
    // Filter stocks with quantity > 0 and run Monte Carlo simulation
    const stocksToSimulate = ownedStocks.filter(stock => stock.quantity > 0);

    try {
      const response = await fetch(`${config.backendUrl}/montePortfolio.php`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ stocks: stocksToSimulate }),
      });
      const data = await response.json();
      console.log('Monte Carlo data:', data);
      if (data.error) {
        console.error(data.error);
      } else {
        setMonteCarloData(data);
      }
    } catch (err) {
      console.error('Failed to run Monte Carlo simulation', err);
    }
  };

  const renderMonteCarloGraph = () => {
    if (!monteCarloData || !monteCarloData.monteCarloResults) {
      return null; // Render nothing if data is incomplete
    }

    const { worstCase, bestCase, medianCase } = monteCarloData.monteCarloResults;

    // Ensure all cases are arrays
    if (!Array.isArray(worstCase) || !Array.isArray(bestCase) || !Array.isArray(medianCase)) {
      console.error('Invalid data structure in monteCarloResults');
      return null;
    }

    const labels = worstCase.map((_, i) => `Day ${i + 1}`); // Generate labels dynamically

    return (
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
            labels: labels,
            datasets: [
              {
                label: "Worst Case",
                data: worstCase,
                borderColor: "red",
                fill: false,
                borderWidth: 1,
              },
              {
                label: "Median Case",
                data: medianCase,
                borderColor: "gray",
                fill: false,
                borderWidth: 1,
              },
              {
                label: "Best Case",
                data: bestCase,
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
    );
  };

  return (
    <div className="portfolio-container">
      <div className="top-bar">
        <button className="logo" onClick={() => navigate("/dashboard")}>
          NightTraders
        </button>
        <div>
          <button
            className="dashboard-button"
            onClick={() => navigate("/dashboard")}
          >
            DASHBOARD
          </button>
          <button className="logout-button" onClick={() => navigate("/")}>
            LOGOUT
          </button>
        </div>
      </div>
      <Box sx={{ p: 3 }}>
        <Typography variant="h4" sx={{ mb: 2, color: 'white' }}>
          Owned Stocks:
        </Typography>
        {ownedStocks.filter((stock) => stock.quantity > 0).length === 0 ? (
          <Typography sx={{ color: 'white' }}>No owned stocks</Typography>
        ) : (
          <Grid container spacing={2}>
            {ownedStocks
              .filter((stock) => stock.quantity > 0)
              .map((stock, index) => (
                <Grid item xs={12} sm={6} md={4} key={index}>
                  <Card sx={{ backgroundColor: "white" }}>
                    <CardContent>
                      <Typography variant="h6">
                        {`${stock.symbol} - Total Invested: $${(
                          stock.quantity * stock.price
                        ).toFixed(2)} (${stock.quantity} shares)`}
                      </Typography>
                      <TextField
                        label="Quantity"
                        type="number"
                        value={quantities[stock.symbol] || ""}
                        onChange={(e) =>
                          handleQuantityChange(stock.symbol, e.target.value)
                        }
                        fullWidth
                        sx={{ mt: 1 }}
                      />
                      <Button
                        variant="contained"
                        onClick={() => handleSave(stock.symbol)}
                        sx={{ mt: 1 }}
                      >
                        Save
                      </Button>
                    </CardContent>
                  </Card>
                </Grid>
              ))}
          </Grid>
        )}
        {showMonteCarloButton && (
          <Button
            variant="contained"
            color="primary"
            onClick={handleRunMonteCarlo}
            sx={{ mt: 3 }}
          >
            Run Monte-Carlo Portfolio
          </Button>
        )}
        {renderMonteCarloGraph()}
      </Box>
    </div>
  );
};

export default Portfolio;
