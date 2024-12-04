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
import "./Dashboard.css"; // Ensure CSS is correctly applied

const Dashboard: React.FC = () => {
  const [stocks, setStocks] = useState<any[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string>("");
  const [filter, setFilter] = useState<string>("all"); // 'all' or 'owned'
  const [ownedStocks, setOwnedStocks] = useState<string[]>([]);
  const navigate = useNavigate();

  /**
   * Handles user logout by making a request to the backend logout endpoint.
   * Upon successful logout, it clears frontend state and redirects to the landing page.
   */
  const handleLogout = async () => {
    try {
      const response = await fetch(`${config.backendUrl}/logout.php`, {
        credentials: "include", // Ensure cookies are sent
      });
      const data = await response.json();
      if (response.ok && data.code === 200) {
        // Clear frontend state to prevent residual data
        setStocks([]);
        setOwnedStocks([]);
        setFilter("all");
        // Redirect to landing page
        navigate("/");
      } else {
        console.error("Logout failed:", data.message);
        alert("Logout failed. Please try again.");
      }
    } catch (error) {
      console.error("Logout error:", error);
      alert("An unexpected error occurred during logout.");
    }
  };

  /**
   * Fetches all available stocks from the backend.
   */
  const fetchStocks = async () => {
    try {
      const response = await fetch(`${config.backendUrl}/getAllStocks.php`, {
        credentials: "include", // Ensure cookies are sent
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
    }
  };

  /**
   * Fetches the owned stocks for the current user from the backend.
   */
  const fetchOwnedStocks = async () => {
    try {
      const response = await fetch(`${config.backendUrl}/getOwnedStocks.php`, {
        credentials: "include", // Ensure cookies are sent
      });
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

  /**
   * Fetches both all stocks and owned stocks concurrently.
   */
  const fetchData = async () => {
    setLoading(true);
    setError("");
    await Promise.all([fetchStocks(), fetchOwnedStocks()]);
    setLoading(false);
  };

  /**
   * useEffect hook to fetch data when the component mounts.
   */
  useEffect(() => {
    fetchData();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []); // Empty dependency array ensures this runs once on mount

  /**
   * Handles navigation to the stock details page when a stock card is clicked.
   * @param symbol - The stock symbol to navigate to.
   */
  const handleStockClick = (symbol: string) => {
    navigate(`/stock/${symbol}`);
  };

  /**
   * Handles changes in the filter dropdown.
   * @param event - The change event from the Select component.
   */
  const handleFilterChange = (event: any) => {
    setFilter(event.target.value);
  };

  /**
   * Filters the stocks based on the selected filter.
   * - "all": Shows all stocks.
   * - "owned": Shows only the stocks owned by the user.
   */
  const filteredStocks = stocks.filter((stock) => {
    if (filter === "owned") {
      return ownedStocks.includes(stock.Symbol);
    }
    return true;
  });

  return (
    <div className="App">
      {/* Top Navigation Bar */}
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

      {/* Main Content Area */}
      <Box sx={{ p: 3, backgroundColor: "#252525", minHeight: "100vh" }}>
        {/* Filter Dropdown */}
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

        {/* Loading Indicator */}
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
          /* Error Message */
          <Typography variant="h6" color="error" align="center">
            {error}
          </Typography>
        ) : (
          /* Stocks Grid */
          <Grid container spacing={2}>
            {filteredStocks.length === 0 ? (
              /* No Stocks Matching Filter */
              <Grid item xs={12}>
                <Typography variant="h6" align="center" color="white">
                  No stocks match the selected filter.
                </Typography>
              </Grid>
            ) : (
              /* Stock Cards */
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
