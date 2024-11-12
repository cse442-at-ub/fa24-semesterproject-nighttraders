// src/pages/StockGraph.tsx
import React from 'react';
import { Line } from 'react-chartjs-2';
import { Box } from '@mui/material';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    ChartData
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
);

interface StockGraphProps {
    stocks: any[];
}

const StockGraph: React.FC<StockGraphProps> = ({ stocks }) => {
    if (!stocks || stocks.length === 0) {
        return null;
    }

    // Process the time series data
    const processedData = stocks
        .map(stock => {
            if (!stock.stockInfo || !stock.monteCarloResults) return null;
            const timeSeriesData = stock.monteCarloResults.scenarios[0] || [];
            return {
                label: stock.stockInfo.Symbol,
                data: timeSeriesData,
                borderColor: getRandomColor(),
                backgroundColor: getRandomColor(),
                fill: false,
                tension: 0.1
            };
        })
        .filter((dataset): dataset is NonNullable<typeof dataset> => dataset !== null);

    if (processedData.length === 0) {
        return null;
    }

    const data: ChartData<'line'> = {
        labels: Array.from(
            { length: processedData[0]?.data.length || 0 },
            (_, i) => `Day ${i + 1}`
        ),
        datasets: processedData
    };

    const options = {
        responsive: true,
        plugins: {
            legend: {
                position: 'top' as const,
            },
            title: {
                display: true,
                text: 'Stock Price Predictions'
            }
        },
        scales: {
            y: {
                beginAtZero: false,
            }
        }
    };

    return (
        <Box sx={{
            backgroundColor: 'white',
            p: 2,
            borderRadius: 2,
            height: '400px'
        }}>
            <Line data={data} options={options} />
        </Box>
    );
};

const getRandomColor = () => {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
};

export default StockGraph;