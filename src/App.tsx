// src/App.tsx
import React from 'react';
import { HashRouter as Router, Routes, Route } from 'react-router-dom';
import Landing from './pages/Landing';
import Register from './pages/Register';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import StockDetails from './pages/StockDetails';
import Portfolio from './pages/Portfolio'; // Import the Portfolio page
import './App.css';

const App: React.FC = () => {
  return (
    <Router>
        {/* Define Routes */}
        <Routes>
          <Route path="/" element={<Landing />} />
          <Route path="/register" element={<Register />} />
          <Route path="/login" element={<Login />} />
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/portfolio" element={<Portfolio />} /> {/* Added Portfolio route */}
          <Route path="/stock/:symbol" element={<StockDetails />} />
        </Routes>
    </Router>
  );
}

export default App;
