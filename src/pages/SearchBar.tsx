import { TextField, IconButton, Box } from "@mui/material";
import { Search } from "@mui/icons-material";
import { useState } from "react";

const SearchBar = () => {
  const [query, setQuery] = useState("");

  const handleSearch = () => {
    console.log("Searching for:", query);
    // Implement your stock search logic here
  };

  return (
    <Box sx={{ display: "flex", alignItems: "center", mt: 2 }}>
      <TextField
        variant="outlined"
        placeholder="Enter Stock Name or Symbol"
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        fullWidth
      />
      <IconButton onClick={handleSearch} sx={{ ml: 1 }}>
        <Search />
      </IconButton>
    </Box>
  );
};

export default SearchBar;