// frontend/pages/Landing.tsx

import React from "react";
import "./Landing.css";
import { useNavigate } from "react-router-dom";

const Landing: React.FC = () => {
  const navigate = useNavigate();
  return (
    <div className="container-div">
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
      <img
        src={require("./How-to-trade-stocks.png")} // Ensure the image path is correct
        alt="Stock Market"
        className="main-image"
      />
      <h1 className="intro-line">
        Welcome to NightTraders, the Winner-Takes-It-All trading platform!
      </h1>
      <div className="landing-content">
        <div className="landing-left-box">
          <h2>What they're saying:</h2>
          <p>TOP 10 STOCKS 2024</p>
          <p>BRANDAU'S BEST BUYS</p>
          <p>NYSE: CSX: An Analysis</p>
          <p>MORE...</p>
        </div>
        <div className="landing-right-box">
          <h2>Want To Beat the Market?</h2>
          <button className="join-btn" onClick={() => navigate("/register")}>
            JOIN NOW
          </button>
        </div>
      </div>
    </div>
  );
};

export default Landing;
