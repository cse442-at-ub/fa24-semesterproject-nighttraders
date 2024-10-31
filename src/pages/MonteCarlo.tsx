// Analysis.tsx
import React, { useState } from "react";
import StockButton from "../Components/StockButton";
import "./MonteCarlo.css"; // Import the CSS file for styling

const Analysis: React.FC = () => {
  const [selectedStock, setSelectedStock] = useState<string | null>(null);
  const [stockPrice, setStockPrice] = useState<number | null>(null);
  const [monteCarloData, setMonteCarloData] = useState<any | null>(null);

  const fetchStockData = async (stockName: string) => {
    // Logic for fetching stock data remains unchanged
  };

  return (
    <div className="analysis-page">
      <button className="logo" onClick={() => (window.location.href = "/")}>
        NightTraders
      </button>
      {selectedStock ? (
        <div>
          <h2>{selectedStock} Analysis</h2>
          <p>Stock Price: ${stockPrice}</p>
          {/* Placeholder for individual stock's Monte Carlo graph */}
          <div className="graph-box">Graph for {selectedStock}</div>
        </div>
      ) : (
        <div>
          <h2>Market Trends</h2>
          {/* Placeholder box for the general Monte Carlo graph */}
          <div className="graph-box">Graph will go here</div>
        </div>
      )}
      <div className="stock-buttons">
        {["AAPL", "GOOGL", "AMZN", "MSFT", "TSLA", "META", "NFLX", "NVDA", "BABA", "JPM"].map(
          (stock, index) => (
            <StockButton key={index} stockName={stock} onClick={fetchStockData} />
          )
        )}
      </div>
    </div>
  );
};

export default Analysis;
