import React, { useState, useEffect } from 'react';
import './App.css';

function App() {
  const [name, setName] = useState(''); // State to hold the fetched name
  const [loading, setLoading] = useState(true); // State for loading status
  const [error, setError] = useState(null); // State to handle errors

  const handleLogout = () => {
    // Logout logic
    alert('You have logged out!');
  };

  // Fetch data from dashboard.php when the component mounts
  // useEffect(() => {
  //   fetch('http://your-server-url/dashboard.php')
  //     .then((response) => {
  //       if (!response.ok) {
  //         throw new Error('Network response was not ok');
  //       }
  //       return response.json();
  //     })
  //     .then((data) => {
  //       setName(data.name);
  //       setLoading(false);
  //     })
  //     .catch((error) => {
  //       setError(error);
  //       setLoading(false);
  //     });
  // }, []);

  // if (loading) {
  //   return <div className="App">Loading...</div>;
  // }

  // if (error) {
  //   return <div className="App">Error: {error.message}</div>;
  // }

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