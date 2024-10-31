// StockButton.tsx
import React from "react";

interface StockButtonProps {
  stockName: string;
  onClick: (stockName: string) => void;
}

const StockButton: React.FC<StockButtonProps> = ({ stockName, onClick }) => {
  return (
    <button className="stock-button" onClick={() => onClick(stockName)}>
      <div>{stockName}</div>
      {/* Placeholder for stock price. Update this with dynamic data if needed */}
    </button>
  );
};

export default StockButton;
