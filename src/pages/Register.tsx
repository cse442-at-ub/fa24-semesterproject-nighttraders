import React, { useState } from "react";
import {
  Avatar,
  Box,
  Button,
  Container,
  CssBaseline,
  Grid,
  TextField,
  Typography,
} from "@mui/material";
import { LockOutlined } from "@mui/icons-material";
import { Link, useNavigate } from "react-router-dom";

const Register = () => {
  const [username, setUsername] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordRepeat, setPasswordRepeat] = useState("");
  const [birthdate, setBirthdate] = useState("");
  const [error, setError] = useState("");
  const navigate = useNavigate();

  const handleRegister = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");

    try {
      const formData = new FormData();
      formData.append("username", username);
      formData.append("email", email);
      formData.append("password", password);
      formData.append("repeat_password", passwordRepeat);
      formData.append("birthday", birthdate);

      const response = await fetch(
        "https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442e/register.php",
        {
          method: "POST",
          body: formData,
          credentials: "include", // Ensures cookies are sent
        }
      );

      const data = await response.json();

      if (data.code === 200) {
        console.log("User registered successfully!", data);
        // Redirect to login instead of dashboard
        navigate("/login", { state: { message: "Registration successful. Please log in." } });
      } else {
        setError(data.error || "Registration failed. Please try again.");
      }
    } catch (error) {
      console.error("Error registering user:", error);
      setError("An unexpected error occurred. Please try again.");
    }
  };

  return (
    <div className="register">
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
            marginTop: 8,
            display: "flex",
            flexDirection: "column",
            alignItems: "center",
          }}
        >
          <Avatar sx={{ m: 1, bgcolor: "secondary.main" }}>
            <LockOutlined />
          </Avatar>
          <Typography component="h1" variant="h5">
            Register
          </Typography>
          {error && (
            <Typography color="error" sx={{ mt: 2 }}>
              {error}
            </Typography>
          )}
          <Box component="form" onSubmit={handleRegister} sx={{ mt: 3 }}>
            <Grid container spacing={2}>
              <Grid item xs={12}>
                <TextField
                  required
                  fullWidth
                  id="username"
                  label="Username"
                  name="username"
                  autoFocus
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                />
              </Grid>
              <Grid item xs={12}>
                <TextField
                  required
                  fullWidth
                  id="email"
                  label="Email Address"
                  name="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                />
              </Grid>
              <Grid item xs={12}>
                <TextField
                  required
                  fullWidth
                  name="password"
                  label="Password"
                  type="password"
                  id="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                />
              </Grid>
              <Grid item xs={12}>
                <TextField
                  required
                  fullWidth
                  name="repeat_password"
                  label="Repeat Password"
                  type="password"
                  id="repeat_password"
                  value={passwordRepeat}
                  onChange={(e) => setPasswordRepeat(e.target.value)}
                />
              </Grid>
              <Grid item xs={12}>
                <TextField
                  required
                  fullWidth
                  id="birthdate"
                  label="Birthdate"
                  name="birthdate"
                  type="date"
                  InputLabelProps={{ shrink: true }}
                  value={birthdate}
                  onChange={(e) => setBirthdate(e.target.value)}
                />
              </Grid>
            </Grid>
            <Button
              type="submit"
              fullWidth
              variant="contained"
              sx={{ mt: 3, mb: 2 }}
            >
              Register
            </Button>
            <Grid container justifyContent="flex-end">
              <Grid item>
                <Link to="/login">Already have an account? Sign in</Link>
              </Grid>
            </Grid>
          </Box>
        </Box>
      </Container>
    </div>
  );
};

export default Register;
