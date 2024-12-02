// src/pages/Portfolio.tsx
import React, { useState, useEffect } from "react";
import { config } from "../config";
import {
  Box,
  Typography,
  TextField,
  Button,
  Card,
  CardContent,
  Grid,
} from "@mui/material";
import { useNavigate } from "react-router-dom";
import "./Portfolio.css";
import StockGraph from "./StockGraph";

interface OwnedStock {
  symbol: string;
  quantity: number;
  price: number;
}

const Portfolio: React.FC = () => {
  const [ownedStocks, setOwnedStocks] = useState<OwnedStock[]>([]);
  const [quantities, setQuantities] = useState<{ [key: string]: number }>({});
  const [showMonteCarloButton, setShowMonteCarloButton] =
    useState<boolean>(false);
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
        console.log("Quantity updated successfully");
      }
    } catch (err) {
      console.error("Failed to update stock quantity", err);
    }
  };

  const handleRunMonteCarlo = async () => {
    // Filter stocks with quantity > 0 and run Monte Carlo simulation
    const stocksToSimulate = ownedStocks.filter((stock) => stock.quantity > 0);

    try {
      const response = await fetch(`${config.backendUrl}/montePortfolio.php`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ stocks: stocksToSimulate }),
      });
      const data = await response.json();
      if (data.error) {
        console.error(data.error);
      } else {
        setMonteCarloData(data);
      }
    } catch (err) {
      console.error("Failed to run Monte Carlo simulation", err);
    }
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
        <Typography variant="h4" sx={{ mb: 2 }}>
          Owned Stocks:
        </Typography>
        {ownedStocks.filter((stock) => stock.quantity > 0).length === 0 ? (
          <Typography>No owned stocks</Typography>
        ) : (
          <Grid container spacing={2}>
            {ownedStocks.map((stock, index) => (
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
        {monteCarloData && (
          <Box sx={{ mt: 3 }}>
            <Typography variant="h5">Portfolio Simulation Results:</Typography>
            <StockGraph stocks={monteCarloData.stocks} />
          </Box>
        )}
      </Box>
    </div>
  );
};

export default Portfolio;
