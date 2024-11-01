// Analysis.tsx
import React, { useState } from "react";
import StockButton from "../Components/StockButton";
import "./Analysis.css"; // Import the CSS file for styling

const Analysis: React.FC = () => {
  const [selectedStock, setSelectedStock] = useState<string | null>(null);
  const [stockPrice, setStockPrice] = useState<number | null>(null);
  const [monteCarloData, setMonteCarloData] = useState<any | null>(null);
  const [error, setError] = useState<string | null>(null);

  const fetchStockData = async (stockName: string) => {
    try {
      setError(null); // Reset error state
      // Update the URL to point to your backend `fetchStock.php` file
      const response = await fetch(
        `http://localhost:3000/backend/fetchStock.php?symbol=${stockName}`,
        {
          method: "GET",
          credentials: "include",
        }
      );

      const data = await response.json();

      if (data.code === 200) {
        // Assuming the stock information includes a "Price" field
        const stockDetails = data.stock;
        setSelectedStock(stockName);
        setStockPrice(stockDetails?.Price ?? 0); // Use the price from the response
        setMonteCarloData(stockDetails?.MonteCarloData ?? {}); // Placeholder for Monte Carlo data
      } else {
        setError(data.error || "Stock couldn't be retrieved.");
      }
    } catch (error) {
      console.error("Error fetching stock data:", error);
      setError("An error occurred while fetching stock data.");
    }
  };

  const handleBackToGeneral = () => {
    setSelectedStock(null);
    setStockPrice(null);
    setMonteCarloData(null);
  };

  return (
    <div className="analysis-page">
      <button className="logo" onClick={() => (window.location.href = "/")}>
        NightTraders
      </button>
      {selectedStock ? (
        <div>
          <h2>{selectedStock} Analysis</h2>
          {error ? (
            <p className="error-message">{error}</p>
          ) : (
            <p>Stock Price: ${stockPrice ? stockPrice.toFixed(2) : "Loading..."}</p>
          )}
          {/* Placeholder for individual stock's Monte Carlo graph */}
          <div className="graph-box">Graph for {selectedStock}</div>
          <button className="back-button" onClick={handleBackToGeneral}>
            Back to General Analysis
          </button>
        </div>
      ) : (
        <div>
          <h2>General Monte Carlo Analysis</h2>
          {/* Placeholder box for the general Monte Carlo graph */}
          <div className="graph-box">Graph will go here</div>
          <div className="stock-buttons">
            {["AAPL", "GOOGL", "AMZN", "MSFT", "TSLA", "META", "NFLX", "NVDA", "BABA", "JPM"].map(
              (stock, index) => (
                <StockButton key={index} stockName={stock} onClick={fetchStockData} />
              )
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default Analysis;
