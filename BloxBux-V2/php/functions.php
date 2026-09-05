<?php
// Fetch games from database
function getGames($user_id) {
    global $conn;
    
    if ($user_id === "NULL") {
        // Get all games
        $result = $conn->query("SELECT * FROM games ORDER BY game_id DESC");
    } else {
        // Get user's games (as starter or player)
        $user_id = $conn->real_escape_string($user_id);
        $result = $conn->query("SELECT * FROM games WHERE starter_id = '$user_id' OR player_id = '$user_id' ORDER BY game_id DESC");
    }
    
    $games = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $games[] = $row;
        }
    }
    
    return $games;
}

// Get user's thumbnail URL
function getUserThumbnail($user_id) {
    global $conn;
    
    $user_id = $conn->real_escape_string($user_id);
    $result = $conn->query("SELECT thumbnail FROM users WHERE user_id = '$user_id'");
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['thumbnail'] ?? 'https://via.placeholder.com/48';
    }
    
    return 'https://via.placeholder.com/48';
}

// Get user's display name
function getName($user_id) {
    global $conn;
    
    $user_id = $conn->real_escape_string($user_id);
    $result = $conn->query("SELECT username FROM users WHERE user_id = '$user_id'");
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['username'] ?? 'Unknown';
    }
    
    return 'Unknown';
}

// Get item information
function getItemInfo($item_id) {
    global $conn;
    
    $item_id = $conn->real_escape_string($item_id);
    $result = $conn->query("SELECT display_name, item_image FROM items WHERE item_id = '$item_id'");
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return ['display_name' => 'Unknown', 'item_image' => 'https://via.placeholder.com/36'];
}
?>
