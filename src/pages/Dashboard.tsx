import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import "./Dashboard.css";

const Dashboard: React.FC = () => {
  const [name, setName] = useState<string>(""); // State to hold the fetched name
  const [loading, setLoading] = useState<boolean>(true); // State for loading status
  const [error, setError] = useState<Error | null>(null); // State to handle errors
  const navigate = useNavigate();

  const handleLogout = async () => {
    try {
      const response = await fetch(
        "https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442e/backend/logout.php",
        {
          method: "POST",
          credentials: "include", // Enables sending cookies
        }
      );

      if (response.ok) {
        console.log("Logged out successfully.");
      } else {
        console.error("Logout failed.");
      }
    } catch (error) {
      console.error("Error during logout:", error);
    }
    navigate("/"); // Redirect after logout
  };

  // Fetch data from dashboard.php when the component mounts
  useEffect(() => {
    fetch(
      "https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442e/backend/dashboard.php",
      {
        method: "GET",
        credentials: "include", // Enables sending cookies
      }
    )
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        if (data.name) {
          setName(data.name);
        } else {
          setError(new Error(data.error || "Failed to fetch user data"));
        }
        setLoading(false);
      })
      .catch((error) => {
        setError(error);
        setLoading(false);
      });
   }, []);

  if (loading) {
    return <div className="App">Loading...</div>;
  }

  if (error) {
    return <div className="App">Error: {error.message}</div>;
  }

  return (
    <div className="App">
      <div className="top-bar">
        <button className="logo" onClick={handleLogout}>
          NightTraders
        </button>
        <button className="logout-button" onClick={handleLogout}>
          Logout
        </button>
      </div>
      <div className="content">
        <h2>Welcome, {name}</h2>
      </div>
    </div>
  );
};

export default Dashboard;