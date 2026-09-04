<?php
session_start();
//Disable Including the File
if (get_included_files()[0] != __FILE__) {return;}

include_once "main.php";
include_once "game_handler.php";
include_once "session_handler.php";
include_once "roblox_handler.php";

// Always return JSON from this endpoint
header('Content-Type: application/json');

if (!$session) {
    jsonError("You are not Logged In!");
}
if (!isset($_POST["type"])) {
    jsonError("400 Bad Request");
}

// Helper to accept both item_id and item_ids keys (client may send either)
function parseItemIdsFromRequest()
{
    if (isset($_POST['item_ids'])) return json_decode($_POST['item_ids'], true);
    if (isset($_POST['item_id'])) return json_decode($_POST['item_id'], true);
    return null;
}

if ($_POST["type"] == "cancel") {
    if (!isset($_POST["game_id"])) {
        jsonError("400 Bad Request");
    }
    $gameInfo = getGameData($_POST["game_id"]);
    if (!$gameInfo) {
        jsonError("Game not found!");
    }
    if ($gameInfo["starter_id"] != $session["user_id"]) {
        jsonError("You are not the host of this game!");
    }
    if ($gameInfo["end_date"]) {
        jsonError("This game is already Completed!");
    }
    $resp = deleteGame($_POST["game_id"]);
    jsonError($resp[0]?false:$resp[1]);
} elseif ($_POST["type"] == "create") {
    // Accept either item_id (legacy) or item_ids (preferred)
    $itemIds = parseItemIdsFromRequest();
    if ($itemIds === null) {
        jsonError("400 Bad Request");
    }
    if (!is_array($itemIds)) {
        jsonError("400 Bad Request");
    }
    if (!isset($_POST["side"])) {
        jsonError("400 Bad Request");
    }
    $side = $_POST["side"];
    if ($side !== "0" && $side !== "1" && $side !== 0 && $side !== 1) {
        jsonError("400 Bad Request");
    }
    // normalize to int 0/1
    $side = intval($side);

    if (count($itemIds) <= 0) {
        jsonError("You need to select some items");
    }

    // createGame should perform all checks (ownership, value, locking items)
    $gameId = createGame($session["user_id"], $side, $itemIds);
    if (!$gameId[0]) {
        jsonError($gameId[1]);
    }
    // Success: return created game id
    echo json_encode(["success" => true, "game_id" => $gameId[1]]);
    exit();
} elseif ($_POST["type"] == "play") {
    // Accept both names
    $itemIds = parseItemIdsFromRequest();
    if ($itemIds === null) {
        jsonError("400 Bad Request");
    }
    if (!is_array($itemIds)) {
        jsonError("400 Bad Request");
    }
    if (!isset($_POST["game_id"])) {
        jsonError("400 Bad Request");
    }
    $gameInfo = getGameData($_POST["game_id"]);
    if (!$gameInfo) {
        jsonError("Game not found!");
    }
    if ($gameInfo["starter_id"] == $session["user_id"]) {
        jsonError("You are the host of this game!");
    }
    if (!$itemIds || count($itemIds) <= 0) {
        jsonError("You need to select some items");
    }
    // playGame should perform all checks and atomically resolve the game
    $playResult = playGame($_POST["game_id"], $session["user_id"], $itemIds);
    if (!$playResult[0]) {
        jsonError($playResult[1]);
    }
    // playResult[1] might contain additional data like winner info
    echo json_encode(["success" => true, "data" => $playResult[1]]);
    exit();
} elseif ($_POST["type"] == "gethtml") {
    if (!isset($_POST["game_id"])) {
        exit();
    }
    $match = getGameData($_POST["game_id"]);
    if (!$match) {
        exit();
    } ?>
    <div id='game<?php echo $match["game_id"];?>' class="<?php echo $session?($match["starter_id"] == $session["user_id"] or $match["player_id"] == $session["user_id"])?"mymatch":"publicmatch":"publicmatch"; ?>" style="justify-content:space-between;">
            <div style="display:flex;flex-direction:column;gap:10px;align-items:center;width:calc(100% - 100px);">
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;justify-content:space-between;width:100%;">
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <img src="<?php echo $match["starter_side"] == 0 ? "./img/gem.png" : "./img/dog.png"; ?>" alt="<?php echo $match["starter_side"] == 0 ? "Gem" : "Dog"; ?>" width="32px" height="32px">
                        <img class="userthumb" src="<?php echo getUserThumbnail($match["starter_id"]); ?>" width="32px" height="32px">
                        <div style="font-size:24px;"><?php echo getName($match["starter_id"]); ?></div>
                        <?php
                        foreach (json_decode($match["starter_items"], true) as $item) :
                        ?>
                            <img src="<?php echo getItemInfo($item["item_id"])["item_image"]; ?>" width="32px" height="32px">
                        <?php endforeach; ?>
                    </div>
                    <?php if ($match["end_date"]) : ?> <div style="font-size:24px;">Value: <?php echo $match["starter_value"]; ?></div> <?php endif; ?>
                </div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;justify-content:space-between;width:100%;">
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <img src="<?php echo $match["starter_side"] == 1 ? "./img/gem.png" : "./img/dog.png"; ?>" alt="<?php echo $match["starter_side"] == 1 ? "Gem" : "Dog"; ?>" width="32px" height="32px">
                        <?php if ($match["end_date"]) : ?>
                            <img class="userthumb" src="<?php echo getUserThumbnail($match["player_id"]); ?>" width="32px" height="32px">
                            <div style="font-size:24px;"><?php echo getName($match["player_id"]); ?></div>
                            <?php
                            $player_items = json_decode($match["player_items"], true);
                            if (!$player_items) {
                                $player_items = [];
                            }
                            foreach ($player_items as $item) :
                            ?>
                                <img src="<?php echo getItemInfo($item["item_id"])["item_image"]; ?>" width="32px" height="32px">
                            <?php endforeach; ?>
                    </div>
                    <div style="font-size:24px;">Value: <?php echo $match["player_value"]; ?></div>
                </div>
            <?php else : ?>
                <?php if (!$session) : ?>
                    <button onclick="login()" class="btn-primary">Join Match (<?php echo $match["starter_value"] - 10 ?> - <?php echo $match["starter_value"] + 10 ?>)</button>
                <?php elseif ($match["starter_id"] != $session["user_id"]) : ?>
                    <button onclick='joinMatch(<?php echo $match["game_id"] . "," . $match["starter_value"]; ?>)' class="btn-primary">Join Match (<?php echo $match["starter_value"] - 10 ?> - <?php echo $match["starter_value"] + 10 ?>)</button>
                <?php else : ?>
                    <button onclick='cancelMatch(<?php echo $match["game_id"]; ?>)' class="btn-primary">Cancel Match</button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
    <?php
}
jsonError("400 Bad Request");
?>
