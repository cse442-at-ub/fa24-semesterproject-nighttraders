// src/pages/StockDetails.tsx
import React, { useState, useEffect } from 'react';
import { config } from '../config';
import { useParams, useNavigate } from 'react-router-dom';
import { Box, Typography, Button, CircularProgress } from '@mui/material';
import { Line } from 'react-chartjs-2';
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

const StockDetails: React.FC = () => {
    const { symbol } = useParams<{ symbol: string }>();
    const [stockInfo, setStockInfo] = useState<any>(null);
    const [loading, setLoading] = useState<boolean>(true);
    const [error, setError] = useState<string>('');
    const [chartData, setChartData] = useState<any>(null);
    const [monteCarloData, setMonteCarloData] = useState<any>(null);

    const navigate = useNavigate();

    useEffect(() => {
        const fetchStockDetails = async () => {
            try {
                const response = await fetch(`${config.backendUrl}/getStockDetails.php?symbol=${symbol}`, {
                    credentials: 'include'
                });
                const data = await response.json();
                if (data.error) {
                    setError(data.error);
                } else {
                    setStockInfo(data.stockInfo);
                    // Prepare chart data
                    const timeSeries = data.stockInfo.TimeSeries['Time Series (Daily)'];
                    const labels = Object.keys(timeSeries).reverse();
                    const prices = labels.map(date => parseFloat(timeSeries[date]['4. close']));
                    setChartData({
                        labels,
                        datasets: [
                            {
                                label: `${symbol} Closing Prices`,
                                data: prices,
                                borderColor: 'rgba(75,192,192,1)',
                                fill: false,
                            },
                        ],
                    });
                }
            } catch (err) {
                console.error(err);
                setError('Failed to fetch stock details.');
            } finally {
                setLoading(false);
            }
        };
        fetchStockDetails();
    }, [symbol]);

    const handleRunMonteCarlo = async () => {
        try {
            const response = await fetch(`${config.backendUrl}/monte.php`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ symbol }),
            });
            const data = await response.json();
            if (data.error) {
                setError(data.error);
            } else {
                // Prepare Monte Carlo chart data
                const scenarios = data.monteCarloResults.scenarios;
                const labels = Array.from({ length: scenarios.worstCase.length }, (_, i) => `Day ${i + 1}`);
                const datasets = [
                    {
                        label: 'Worst Case',
                        data: scenarios.worstCase,
                        borderColor: 'red',
                        fill: false,
                        borderWidth: 1,
                    },
                    {
                        label: 'Average Case',
                        data: scenarios.medianCase,
                        borderColor: 'gray',
                        fill: false,
                        borderWidth: 1,
                    },
                    {
                        label: 'Best Case',
                        data: scenarios.bestCase,
                        borderColor: 'green',
                        fill: false,
                        borderWidth: 1,
                    },
                ];

                setMonteCarloData({
                    labels,
                    datasets,
                });
            }
        } catch (err) {
            console.error(err);
            setError('Failed to run Monte Carlo simulation.');
        }
    };

    const getRandomColor = () => {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    };

    return (
        <div style={{ backgroundColor: '#252525', minHeight: '100vh', color: 'white' }}>
            <div className="top-bar">
                <Typography variant="h6" component="div">
                    {symbol} Details
                </Typography>
                <button className="back-button" onClick={() => navigate('/dashboard')}>
                    Back to Dashboard
                </button>
            </div>
            <Box sx={{ p: 3 }}>
                {loading ? (
                    <Box display="flex" justifyContent="center" alignItems="center" minHeight="200px">
                        <CircularProgress />
                    </Box>
                ) : error ? (
                    <Typography variant="h6" color="error" align="center">
                        {error}
                    </Typography>
                ) : (
                    <>
                        {chartData && (
                            <Box sx={{ backgroundColor: 'white', p: 2, borderRadius: 2 }}>
                                <Line data={chartData} />
                            </Box>
                        )}
                        <Button variant="contained" sx={{ mt: 2 }} onClick={handleRunMonteCarlo}>
                            Run Monte-Carlo Simulation
                        </Button>
                        {monteCarloData && (
                            <Box sx={{ backgroundColor: 'white', p: 2, borderRadius: 2, mt: 2 }}>
                                <Line data={monteCarloData} />
                            </Box>
                        )}
                        <Box sx={{ mt: 2 }}>
                            <Typography variant="h6">Stock Information</Typography>
                            <Typography>Symbol: {stockInfo.Symbol}</Typography>
                            <Typography>Name: {stockInfo.Name}</Typography>
                            <Typography>Exchange: {stockInfo.Exchange}</Typography>
                            <Typography>Sector: {stockInfo.Sector}</Typography>
                            <Typography>Industry: {stockInfo.Industry}</Typography>
                            <Typography>EPS: {stockInfo.EPS}</Typography>
                            <Typography>Latest Quarter: {stockInfo.LatestQuarter}</Typography>
                            <Typography>52 Week High: {stockInfo['52WeekHigh']}</Typography>
                            <Typography>52 Week Low: {stockInfo['52WeekLow']}</Typography>
                            <Typography>Analyst Target Price: {stockInfo.AnalystTargetPrice}</Typography>
                        </Box>
                    </>
                )}
            </Box>
        </div>
    );
};

export default StockDetails;
