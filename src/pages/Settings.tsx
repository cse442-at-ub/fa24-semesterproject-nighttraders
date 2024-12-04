import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Box, Typography, TextField, Button, CircularProgress } from '@mui/material';
import { config } from '../config';

const Settings: React.FC = () => {
    const [username, setUsername] = useState<string>('');
    const [email, setEmail] = useState<string>('');
    const [password, setPassword] = useState<string>('');
    const [loading, setLoading] = useState<boolean>(false);
    const [message, setMessage] = useState<string>('');
    const [error, setError] = useState<string>('');
    const navigate = useNavigate();

    const handleSave = async () => {
        setLoading(true);
        setMessage('');
        setError('');
        try {
            const response = await fetch(`${config.backendUrl}/updateSettings.php`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username,
                    email,
                    password,
                }),
            });
            const data = await response.json();
            if (data.error) {
                setError(data.message || 'Failed to update settings.');
            } else {
                setMessage('Settings updated successfully!');
            }
        } catch (err) {
            console.error(err);
            setError('An error occurred while updating settings.');
        } finally {
            setLoading(false);
        }
    };

    const handleDeactivate = async () => {
        const confirm = window.confirm(
            'Are you sure you want to deactivate your account? This action cannot be undone.'
        );
        if (!confirm) return;

        setLoading(true);
        setError('');
        try {
            const response = await fetch(`${config.backendUrl}/deactivateAccount.php`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            const data = await response.json();
            if (data.error) {
                setError(data.message || 'Failed to deactivate account.');
            } else {
                navigate('/'); // Redirect to the login page after deactivation
            }
        } catch (err) {
            console.error(err);
            setError('An error occurred while deactivating the account.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div style={{ backgroundColor: '#252525', minHeight: '100vh', color: 'white' }}>
            <div className="top-bar">
                <button className="logo" onClick={() => navigate("/dashboard")}>
                    NightTraders
                </button>
                <button className="logout-button" onClick={() => navigate("/")}>
                    LOGOUT
                </button>
            </div>
            <Box sx={{ p: 3 }}>
                <Typography variant="h4" sx={{ mb: 3 }}>
                    Settings
                </Typography>
                <Box sx={{ maxWidth: '500px', mx: 'auto' }}>
                    <TextField
                        fullWidth
                        label="Username"
                        variant="outlined"
                        value={username}
                        onChange={(e) => setUsername(e.target.value)}
                        sx={{ mb: 2, backgroundColor: 'white', borderRadius: 1 }}
                    />
                    <TextField
                        fullWidth
                        label="Email"
                        variant="outlined"
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        sx={{ mb: 2, backgroundColor: 'white', borderRadius: 1 }}
                    />
                    <TextField
                        fullWidth
                        label="New Password (optional)"
                        variant="outlined"
                        type="password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        sx={{ mb: 2, backgroundColor: 'white', borderRadius: 1 }}
                    />
                    <Button
                        variant="contained"
                        fullWidth
                        sx={{ mt: 2, mb: 2 }}
                        onClick={handleSave}
                        disabled={loading}
                    >
                        {loading ? <CircularProgress size={24} color="inherit" /> : 'Save Changes'}
                    </Button>
                    <Button
                        variant="outlined"
                        fullWidth
                        color="error"
                        sx={{ mt: 2 }}
                        onClick={handleDeactivate}
                        disabled={loading}
                    >
                        Deactivate Account
                    </Button>
                </Box>
                {message && (
                    <Typography sx={{ color: 'green', mt: 2, textAlign: 'center' }}>
                        {message}
                    </Typography>
                )}
                {error && (
                    <Typography sx={{ color: 'red', mt: 2, textAlign: 'center' }}>
                        {error}
                    </Typography>
                )}
            </Box>
        </div>
    );
};

export default Settings;
