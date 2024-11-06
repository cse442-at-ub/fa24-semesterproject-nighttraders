import React, { useState } from "react";
import { Container, Box, Typography, Button, TextField } from "@mui/material";
import { useNavigate } from "react-router-dom";

const MonteCarlo = () => {
  const [stockSymbol, setStockSymbol] = useState("");
  const navigate = useNavigate();

  const handleAnalysis = async () => {
    // Replace with your backend endpoint
    const response = await fetch(
      "https://your-backend-endpoint/monte-carlo-analysis",
      {
        method: "POST",
        body: JSON.stringify({ stockSymbol }),
        headers: {
          "Content-Type": "application/json",
        },
      }
    );

    if (response.ok) {
      // Redirect to the results page (or handle it according to your design)
      navigate("/monte-carlo-results");
    } else {
      console.error("Error performing Monte Carlo analysis");
    }
  };

  return (
    <div style={{ backgroundColor: "#252525", minHeight: "100vh" }}>
      <div className="top-bar">
        <button className="logo" onClick={() => navigate("/")}>
          NightTraders
        </button>
        
      </div>
      <Container component="main" maxWidth="xs">
        <Box
          sx={{
            mt: 8,
            display: "flex",
            flexDirection: "column",
            alignItems: "center",
            padding: 3,
            borderRadius: 2,
          }}
        >
          <Typography component="h1" variant="h5" sx={{ color: '#ffffff' }}>
            Monte Carlo Analysis
          </Typography>
          <TextField
            margin="normal"
            required
            fullWidth
            id="stockSymbol"
            label="Enter Stock Name or Symbol"
            value={stockSymbol}
            onChange={(e) => setStockSymbol(e.target.value)}
          />
          <Button
            type="button"
            fullWidth
            variant="contained"
            sx={{ mt: 3, mb: 2 }}
            onClick={handleAnalysis}
          >
            Monte Carlo Analysis
          </Button>
        </Box>
      </Container>
    </div>
  );
};

export default MonteCarlo;
