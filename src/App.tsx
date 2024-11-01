//"homepage": "/CSE442/2024-Fall/cse-442e/",

import React from 'react';
// import { HashRouter as Router, Routes, Route } from 'react-router-dom';
import {HashRouter as Router, Routes, Route, useNavigate } from 'react-router-dom';
// import { BrowserRouter as Router, Routes, Route, useNavigate } from 'react-router-dom'; 
import Landing from './pages/Landing';
import Register from './pages/Register';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import MonteCarlo from "./pages/Analysis";

import './App.css';
import Analysis from './pages/Analysis';

const App: React.FC = () => {
  return (
    <Router>
        {/* Define Routes */}
        <Routes>
          <Route path="/" element={<Landing />} />
          <Route path="/register" element={<Register />} />
          <Route path="/login" element={<Login />} />
          <Route path="/dashboard" element={<Dashboard />}/> 
          <Route path="/analysis" element={<Analysis />} />

        </Routes>
    </Router>
  );
}
// import React from 'react';
// import './pages/Landing.css';

// const Landing: React.FC = () => {
//   return (
//     <div className="container-div">
//       <div className="top-bar">
//         <h1>NightTraders</h1>
//         <button className="register-button">REGISTER</button>
//         <button className="signin-button">SIGN IN</button>
//       </div>
//       <img 
//         src="/How-to-trade-stocks.png" 
//         alt="Stock Market" 
//         className="main-image"  
//       />
//       <h1 className='intro-line'>Welcome to NightTraders, the Winner-Takes-It-All trading platform!</h1>
//       <div className="content">
//         <div className="left-box">
//           <h2>What they're saying:</h2>
//           <p>TOP 10 STOCKS 2024</p>
//           <p>BRANDAU'S BEST BUYS</p>
//           <p>NYSE: CSX: An Analysis</p>
//           <p>MORE...</p>
//         </div>
//         <div className="right-box">
//           <h2>Want To Beat the Market?</h2>
//           <button className="join-btn">JOIN NOW</button>
//         </div>
//       </div>
//     </div>
//   );
// }

export default App;