// src/pages/Dashboard.tsx
import React, { useState, useEffect } from "react";
import { config } from "../config";
import {
  Grid,
  CircularProgress,
  Typography,
  Box,
  Card,
  CardContent,
  CardActionArea,
  MenuItem,
  Select,
  FormControl,
  InputLabel,
} from "@mui/material";
import { useNavigate } from "react-router-dom";
import "./Dashboard.css";

const Dashboard: React.FC = () => {
  const [stocks, setStocks] = useState<any[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string>("");
  const navigate = useNavigate();
  const [filter, setFilter] = useState<string>("all"); // 'all' or 'owned'
  const [ownedStocks, setOwnedStocks] = useState<string[]>([]);

  const handleLogout = async () => {
    try {
      await fetch(`${config.backendUrl}/logout.php`, {
        credentials: "include",
      });
      navigate("/");
    } catch (error) {
      console.error("Logout error:", error);
    }
  };

  useEffect(() => {
    const fetchStocks = async () => {
      try {
        const response = await fetch(`${config.backendUrl}/getAllStocks.php`, {
          credentials: "include",
        });
        const data = await response.json();
        if (data.error) {
          setError(data.error);
        } else {
          setStocks(data.stocks);
        }
      } catch (err) {
        console.error(err);
        setError("Failed to fetch stock data.");
      } finally {
        setLoading(false);
      }
    };

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
          // Extract symbols with quantity > 0
          const ownedSymbols = data.OwnedStocks.filter(
            (stock: any) => stock.quantity > 0
          ).map((stock: any) => stock.symbol);
          setOwnedStocks(ownedSymbols);
        }
      } catch (err) {
        console.error("Failed to fetch owned stocks", err);
      }
    };

    fetchStocks();
    fetchOwnedStocks();
  }, []);

  const handleStockClick = (symbol: string) => {
    navigate(`/stock/${symbol}`);
  };

  const handleFilterChange = (event: any) => {
    setFilter(event.target.value);
  };

  // Filter stocks based on selected filter
  const filteredStocks = stocks.filter((stock) => {
    if (filter === "owned") {
      return ownedStocks.includes(stock.Symbol);
    }
    return true;
  });

  return (
    <div className="App">
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
          <button className="logout-button" onClick={handleLogout}>
            LOGOUT
          </button>
        </div>
      </div>
      <Box sx={{ p: 3, backgroundColor: "#252525", minHeight: "100vh" }}>
        <Box sx={{ display: "flex", justifyContent: "flex-end", mb: 2 }}>
          <FormControl variant="outlined" size="small" sx={{ minWidth: 150 }}>
            <InputLabel sx={{ color: "white" }}>Filter</InputLabel>
            <Select
              value={filter}
              onChange={handleFilterChange}
              label="Filter"
              sx={{
                color: "white",
                ".MuiOutlinedInput-notchedOutline": { borderColor: "white" },
                "&:hover .MuiOutlinedInput-notchedOutline": {
                  borderColor: "white",
                },
                ".MuiSvgIcon-root": { color: "white" },
              }}
            >
              <MenuItem value="all">All Stocks</MenuItem>
              <MenuItem value="owned">Owned Stocks</MenuItem>
            </Select>
          </FormControl>
        </Box>
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
          <Grid container spacing={2}>
            {filteredStocks.length === 0 ? (
              <Grid item xs={12}>
                <Typography variant="h6" align="center" color="white">
                  No stocks match the selected filter.
                </Typography>
              </Grid>
            ) : (
              filteredStocks.map((stock, index) => (
                <Grid item xs={12} sm={6} md={4} key={index}>
                  <Card sx={{ height: "100%", backgroundColor: "white" }}>
                    <CardActionArea
                      onClick={() => handleStockClick(stock.Symbol)}
                    >
                      <CardContent>
                        <Typography variant="h6" component="div" gutterBottom>
                          {stock.Symbol}
                        </Typography>
                        <Typography variant="body2" color="text.secondary">
                          {stock.Name}
                        </Typography>
                        <Typography variant="body2">
                          Exchange: {stock.Exchange}
                        </Typography>
                        <Typography variant="body2">
                          Sector: {stock.Sector}
                        </Typography>
                      </CardContent>
                    </CardActionArea>
                  </Card>
                </Grid>
              ))
            )}
          </Grid>
        )}
      </Box>
    </div>
  );
};

export default Dashboard;
