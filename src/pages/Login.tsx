// frontend/pages/Login.tsx

import { config } from '../config';
import React, { useState } from "react";
import { LockOutlined } from "@mui/icons-material";
import {
  Container,
  CssBaseline,
  Box,
  Avatar,
  Typography,
  TextField,
  Button,
  Grid,
} from "@mui/material";
import { Link, useNavigate, useLocation } from "react-router-dom";

const Login = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [errorMessage, setErrorMessage] = useState("");
  const navigate = useNavigate();
  const location = useLocation();

  const state = location.state as { message?: string };
  const successMessage = state?.message || "";

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMessage("");

    try {
      const formData = new FormData();
      formData.append("email", email);
      formData.append("password", password);
      formData.append("login", "true");

      const response = await fetch(`${config.backendUrl}/login.php`, {
        method: "POST",
        body: formData,
        credentials: "include", // Include cookies for session management
      });

      const data = await response.json();

      if (response.ok && data.code === 200 && data.message === "Login successful") {
        console.log("Login successful!", data);
        navigate("/dashboard");
      } else if (data.message === "Already logged in") {
        setErrorMessage("You are already logged in. Please log out first.");
      } else {
        setErrorMessage(
          data.error || "Invalid email or password. Please try again."
        );
      }
    } catch (error) {
      console.error("Error during login:", error);
      setErrorMessage("Something went wrong. Please try again later.");
    }
  };

  return (
    <div style={{ backgroundColor: "#252525", minHeight: "100vh" }}>
      <div className="top-bar">
        <button className="logo" onClick={() => navigate("/")}>
          NightTraders
        </button>
        <button
          className="register-button"
          onClick={() => navigate("/register")}
        >
          REGISTER
        </button>
        <button className="signin-button" onClick={() => navigate("/login")}>
          SIGN IN
        </button>
      </div>
      <Container component="main" maxWidth="xs">
        <CssBaseline />
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
          <Avatar sx={{ m: 1, bgcolor: "secondary.main" }}>
            <LockOutlined />
          </Avatar>
          <Typography component="h1" variant="h5">
            Login
          </Typography>
          {successMessage && (
            <Typography color="success.main" variant="body2" sx={{ mt: 2 }}>
              {successMessage}
            </Typography>
          )}
          {errorMessage && (
            <Typography color="error" variant="body2" sx={{ mt: 2 }}>
              {errorMessage}
            </Typography>
          )}
          <Box component="form" onSubmit={handleLogin} sx={{ mt: 1 }}>
            <TextField
              margin="normal"
              required
              fullWidth
              id="email"
              label="Email Address"
              name="email"
              autoFocus
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
            <TextField
              margin="normal"
              required
              fullWidth
              id="password"
              name="password"
              label="Password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
            <Button
              type="submit"
              fullWidth
              variant="contained"
              sx={{ mt: 3, mb: 2 }}
            >
              Login
            </Button>
            <Grid container justifyContent="flex-end">
              <Grid item>
                <Link to="/register">Don't have an account? Register</Link>
              </Grid>
            </Grid>
          </Box>
        </Box>
      </Container>
    </div>
  );
};

export default Login;
