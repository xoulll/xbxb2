<?php
//Stop Direct Access to the File
//Works only in PHP 5.0 and Up
if (get_included_files()[0] == __FILE__) {
    exit("<h1>Access Denied</h1>");
}

//Stop Including This File Twice
if (defined(strtoupper(basename(__FILE__, ".php")) . "_PHP")) {
    return True;
}
define(strtoupper(basename(__FILE__, ".php")) . "_PHP", True);

include_once "database.php";

//Get Roblox User Info From Name
function getRobloxUserInfo($name)
{
    $url = "https://users.roblox.com/v1/usernames/users";
    $data = json_encode([
        "usernames" => [$name],
        "excludeBannedUsers" => false
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: Mozilla/5.0'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($result === false || $httpCode !== 200) {
        error_log("Roblox API Error: HTTP $httpCode for username: $name");
        return false;
    }

    $result = json_decode($result, true);

    // The new API response includes an array under 'data'
    if (isset($result["data"]) && count($result["data"]) > 0 && isset($result["data"][0]["id"])) {
        // Return the first matched user info
        return [
            "Id" => $result["data"][0]["id"],
            "Username" => $result["data"][0]["name"]
        ];
    }

    error_log("Roblox API Response for $name: " . print_r($result, true));
    return false;
}

//Get Roblox User Id From Name
function getUserId($name)
{
    global $conn;
    $result = $conn->queryPrepared("SELECT `user_id` FROM `user` WHERE `username` = ?", [$name]);
    if ($result->num_rows > 0) {
        $result = $result->fetch_assoc();
        return $result["user_id"];
    }
    $result = getRobloxUserInfo($name);
    if ($result) {
        $conn->queryPrepared("INSERT INTO `user` (`username`, `user_id`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `username` = ?", [$result["Username"], $result["Id"], $result["Username"]]);
        return $result["Id"];
    }
    return false;
}

//Get Name from User Id
function getName($user_id)
{
    global $conn;
    $result = $conn->queryPrepared("SELECT `username` FROM `user` WHERE `user_id` = ?", [$user_id]);
    if ($result->num_rows > 0) {
        $result = $result->fetch_assoc();
        return $result["username"];
    }
    $url = "https://users.roblox.com/v1/users/" . $user_id;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result, true);
    if (isset($result["name"])) {
        $conn->queryPrepared("INSERT INTO `user` (`username`, `user_id`) VALUES (?, ?)", [$result["name"], $result["id"]]);
        return $result["name"];
    }
    return false;
}

//Get Roblox User Description from Id
function getUserDescription($id)
{
    $url = "https://users.roblox.com/v1/users/" . $id;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result, true);
    if (isset($result["description"])) {
        return $result["description"];
    }
    return false;
}

//Get User Description from Id from Database
function getUserDescriptionDatabase($id)
{
    global $conn;
    $result = $conn->queryPrepared("SELECT `description` FROM `user` WHERE `user_id` = ?", [$id]);
    if ($result->num_rows > 0) {
        $result = $result->fetch_assoc();
        return $result["description"];
    }
    return false;
}

//Get Roblox User Description from name
function getUserDescriptionFromName($name)
{
    $id = getUserId($name);
    if ($id) {
        return getUserDescription($id);
    }
    return false;
}

//Get User Description from name from Database
function getUserDescriptionFromNameDatabase($name)
{
    global $conn;
    $result = $conn->queryPrepared("SELECT `description` FROM `user` WHERE `username` = ?", [$name]);
    if ($result->num_rows > 0) {
        $result = $result->fetch_assoc();
        return getUserDescriptionDatabase($result["description"]);
    }
    return false;
}

//Get Roblox User Thumbnail from Id
function getUserThumbnail($id, $size = "420x420", $fresh = false)
{
    global $conn;
    if (!$fresh) {
        $result = $conn->queryPrepared("SELECT `thumbnail` FROM `user` WHERE `user_id` = ?", [$id]);
        if ($result->num_rows > 0) {
            $result = $result->fetch_assoc();
            if ($result["thumbnail"]) {
                return $result["thumbnail"];
            }
        }
    }
    $url = "https://thumbnails.roblox.com/v1/users/avatar-headshot?userIds=" . $id . "&size=" . $size . "&format=Png&isCircular=false";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result, true);
    if (isset($result["data"])) {
        if ($result["data"][0]["state"] == "Completed") {
            $conn->queryPrepared("UPDATE `user` SET `thumbnail` = ? WHERE `user_id` = ?", [$result["data"][0]["imageUrl"], $id]);
            return $result["data"][0]["imageUrl"];
        } else {
            return getUserThumbnail($id, $size,$fresh);
        }
    }
    return false;
}

//Get Roblox User Thumbnail from Name
function getUserThumbnailFromName($name, $size = "420x420", $fresh = false)
{
    $id = getUserId($name);
    if ($id) {
        return getUserThumbnail($id, $size, $fresh);
    }
    return false;
}

//Check If String is in User Description
function checkUserDescription($id, $string)
{
    $found = false;
    $description = getUserDescriptionDatabase($id);
    if ($description) {
        $found = strpos($description, $string) !== false;
    }
    if (!$found) {
        $description = getUserDescription($id);
        if ($description) {
            $found = strpos($description, $string) !== false;
        }
    }
    return $found;
}

//Check if String is in User Description From Name
function checkUserDescriptionFromName($name, $string)
{
    $id = getUserId($name);
    if ($id) {
        return checkUserDescription($id, $string);
    }
    return false;
}

//Get Asset Thumbnail from Id
function getAssetThumbnail($id, $size = "420x420")
{
    $url = "https://thumbnails.roblox.com/v1/assets?assetIds=" . $id . "&size=" . $size . "&format=Png&isCircular=false";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result, true);
    if (isset($result["data"]) and count($result["data"]) > 0) {
        if ($result["data"][0]["state"] == "Completed") {
            return $result["data"][0]["imageUrl"];
        } else {
            return getAssetThumbnail($id, $size);
        }
    }
    return false;
}