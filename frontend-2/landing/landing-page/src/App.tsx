import TopNav from './components/TopNav';
import './App.css';
// function App() {
//   return <div><TopNav /></div>;
// }

function App() {
  return (
    <div className="container-div">
      <div className="top-nav"><TopNav /></div>
      <h1 className='intro-line'>Welcome to NightTraders, the winner-takes-it-all trading platform</h1>
      {/* <header className="header">
        <div className="header-content">
          <h1>NightTraders</h1>
        </div>
      </header> */}
      {/* <img className="money-hands" src="../../public/trading-image.png" alt="Money exchanging hands" /> */}
      <div className="content">
        <div className="left-box">
          <h2>What they're saying:</h2>
          <p>TOP 10 STOCKS 2024</p>
          <p>BRANDAU'S BEST BUYS</p>
          <p>NYSE: CSX: An Analysis</p>
          <p>MORE...</p>
        </div>
        <div className="right-box">
          <h2>Want To Beat the Market?</h2>
          <button className="join-btn">JOIN NOW</button>
        </div>
      </div>
    </div>
  );
}

export default App;