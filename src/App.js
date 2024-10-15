import React, { useState, useEffect } from 'react';
import './App.css';

function App() {
  const [name, setName] = useState(''); // State to hold the fetched name
  const [loading, setLoading] = useState(true); // State for loading status
  const [error, setError] = useState(null); // State to handle errors

  const handleLogout = () => {
    fetch('https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442e/fa24-semesterproject-nighttraders/build/logout.php', {
      method: 'POST',
      credentials: 'include',
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error('Logout failed.');
        }
        // Handle successful logout, e.g., redirect or display a message
        alert('You have logged out!');
        // Optionally, redirect to login page
        window.location.href = '/login'; // Adjust this URL as necessary
      })
      .catch((error) => {
        console.error('Logout error:', error);
        alert('There was an issue logging out.');
      });
  };

  // Fetch data from dashboard.php when the component mounts
  useEffect(() => {
    fetch('https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442e/fa24-semesterproject-nighttraders/build/dashboard.php')
      .then((response) => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then((data) => {
        setName(data.name);
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
        <h1>NightTraders</h1>
        <button className="logout-button" onClick={handleLogout}>Logout</button>
      </div>
      <div className="content">
        <h2>Welcome, {name}</h2>
      </div>
    </div>
  );
}

export default App;