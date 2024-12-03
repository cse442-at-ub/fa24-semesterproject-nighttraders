// src/pages/StockCard.tsx
import React from "react";
import { Card, CardContent, Typography } from "@mui/material";

interface StockCardProps {
  stock: any;
}

const StockCard: React.FC<StockCardProps> = ({ stock }) => {
  if (!stock.stockInfo || !stock.monteCarloResults) {
    return null;
  }

  const { stockInfo, monteCarloResults } = stock;

  return (
    <Card sx={{ height: "100%", backgroundColor: "white" }}>
      <CardContent>
        <Typography variant="h6" component="div" gutterBottom>
          {stockInfo.Symbol}
        </Typography>
        <Typography variant="body2" color="text.secondary">
          {stockInfo.Name}
        </Typography>
        <Typography variant="body2">Predicted Range:</Typography>
        <Typography variant="body2" color="success.main">
          High: ${monteCarloResults.best.toFixed(2)}
        </Typography>
        <Typography variant="body2" color="error.main">
          Low: ${monteCarloResults.worst.toFixed(2)}
        </Typography>
        <Typography variant="body2">
          Average: ${monteCarloResults.average.toFixed(2)}
        </Typography>
      </CardContent>
    </Card>
  );
};

export default StockCard;
