// Analysis.tsx
import React, { useState } from "react";
import StockButton from "../Components/StockButton";
import "./Analysis.css"; // Import the CSS file for styling

const Analysis: React.FC = () => {
  const [selectedStock, setSelectedStock] = useState<string | null>(null);
  const [stockPrice, setStockPrice] = useState<number | null>(null);
  const [monteCarloData, setMonteCarloData] = useState<any | null>(null);

  const fetchStockData = async (stockName: string) => {
    try {
      // Simulate fetching stock price and Monte Carlo data from backend
      // Replace this with actual API requests
      const simulatedStockPrice = Math.random() * 1000; // Random price for demonstration
      const simulatedMonteCarloData = {}; // Placeholder for Monte Carlo data

      // Update state with fetched data
      setSelectedStock(stockName);
      setStockPrice(simulatedStockPrice);
      setMonteCarloData(simulatedMonteCarloData);
    } catch (error) {
      console.error("Error fetching stock data:", error);
    }
  };

  const handleBackToGeneral = () => {
    // Reset state to go back to the general analysis view
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
          <p>Stock Price: ${stockPrice ? stockPrice.toFixed(2) : "Loading..."}</p>
          {/* Placeholder for individual stock's Monte Carlo graph */}
          <div className="graph-box">Graph for {selectedStock}</div>
          {/* Button to return to the general analysis page */}
          <button className="back-button" onClick={handleBackToGeneral}>
            Back to General Analysis
          </button>
        </div>
      ) : (
        <div>
          <h2>General Monte Carlo Analysis</h2>
          {/* Placeholder box for the general Monte Carlo graph */}
          <div className="graph-box">Graph will go here</div>
          {/* Stock buttons displayed only in the general analysis view */}
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
