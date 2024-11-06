// src/pages/Dashboard.tsx
import React, { useState, useEffect } from 'react';
import { config } from '../config';
import { Grid, CircularProgress, Typography, Box, Card, CardContent, CardActionArea } from '@mui/material';
import { useNavigate } from 'react-router-dom';
import './Dashboard.css';

const Dashboard: React.FC = () => {
    const [stocks, setStocks] = useState<any[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [error, setError] = useState<string>('');
    const navigate = useNavigate();

    const handleLogout = async () => {
        try {
            await fetch(`${config.backendUrl}/logout.php`, {
                credentials: 'include'
            });
            navigate('/');
        } catch (error) {
            console.error('Logout error:', error);
        }
    };

    useEffect(() => {
        const fetchStocks = async () => {
            try {
                const response = await fetch(`${config.backendUrl}/getAllStocks.php`, {
                    credentials: 'include'
                });
                const data = await response.json();
                if (data.error) {
                    setError(data.error);
                } else {
                    setStocks(data.stocks);
                }
            } catch (err) {
                console.error(err);
                setError('Failed to fetch stock data.');
            } finally {
                setLoading(false);
            }
        };
        fetchStocks();
    }, []);

    const handleStockClick = (symbol: string) => {
        navigate(`/stock/${symbol}`);
    };

    return (
        <div className="App">
            <div className="top-bar">
                <Typography variant="h6" component="div">
                    NightTraders Dashboard
                </Typography>
                <button className="logout-button" onClick={handleLogout}>
                    Logout
                </button>
            </div>

            <Box sx={{ p: 3, backgroundColor: '#252525', minHeight: '100vh' }}>
                {loading ? (
                    <Box display="flex" justifyContent="center" alignItems="center" minHeight="200px">
                        <CircularProgress />
                    </Box>
                ) : error ? (
                    <Typography variant="h6" color="error" align="center">
                        {error}
                    </Typography>
                ) : (
                    <Grid container spacing={2}>
                        {stocks.map((stock, index) => (
                            <Grid item xs={12} sm={6} md={4} key={index}>
                                <Card sx={{ height: '100%', backgroundColor: 'white' }}>
                                    <CardActionArea onClick={() => handleStockClick(stock.Symbol)}>
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
                        ))}
                    </Grid>
                )}
            </Box>
        </div>
    );
};

export default Dashboard;
