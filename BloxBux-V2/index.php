<?php
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

//Disable Including the File
if (get_included_files()[0] != __FILE__) {
    return;
}
?>
<?php include "layout/head.php"; ?>
<?php include_once "php/game_handler.php"; ?>
<?php
echo "<div class='hidden'>" . uniqid() . "</div>";
?>

<div class="coinflip-wrapper">
    <div class="coin-area">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h1 style="margin:0;">Xflips — Coinflip</h1>
            <div style="display:flex;gap:12px;align-items:center;">
                <button onclick='<?php echo $session ? "createMatch()" : "login()"; ?>' class="btn-primary">Create Match</button>
                <?php if ($session) : ?><button onclick='toggleMatches()' id='matchbtn' class="btn-secondary">My Matches</button><?php endif; ?>
            </div>
        </div>

        <div style="margin-top:18px;display:flex;gap:18px;align-items:flex-start;">
            <div style="flex:1;">
                <div class="match-list" id="matchList">
                    <?php
                    if ($session) {
                        $matches = getGames($session["user_id"]);
                    } else {
                        $matches = getGames("NULL");
                    }
                    foreach ($matches as $match) :
                    ?>
                        <div id='game<?php echo $match["game_id"]; ?>' class="publicmatch" style="display:flex;justify-content:space-between;align-items:center;padding:12px;margin-bottom:12px;border-radius:10px;">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div style="width:88px;">
                                    <div class="big-coin">
                                        <img src="<?php echo $match["winner_side"] == 0 ? './img/gem.png' : './img/dog.png'; ?>" loading="lazy">
                                    </div>
                                </div>
                                <div style="display:flex;flex-direction:column;">
                                    <div style="font-weight:700;font-size:18px;"><?php echo getName($match["starter_id"]);
                                    if ($match["player_id"]) { echo " vs " . getName($match["player_id"]); } ?></div>
                                    <div style="margin-top:8px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                        <?php foreach (json_decode($match["starter_items"], true) as $item) : ?>
                                            <img src="<?php echo getItemInfo($item["item_id"])["item_image"]; ?>" width="48" height="48" loading="lazy">
                                        <?php endforeach; ?>
                                        <?php if ($match["end_date"]) : foreach (json_decode($match["player_items"], true) as $item) : ?>
                                            <img src="<?php echo getItemInfo($item["item_id"])["item_image"]; ?>" width="48" height="48" loading="lazy">
                                        <?php endforeach; endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
                                <?php if (!$match["end_date"]) : ?>
                                    <?php if (!$session) : ?>
                                        <button onclick="login()" class="btn-primary">Join Match (<?php echo $match["starter_value"] - 10 ?> - <?php echo $match["starter_value"] + 10 ?>)</button>
                                    <?php elseif ($match["starter_id"] != $session["user_id"]) : ?>
                                        <button onclick='joinMatch(<?php echo $match["game_id"] . "," . $match["starter_value"]; ?>)' class="btn-primary">Join Match</button>
                                    <?php else : ?>
                                        <button onclick='cancelMatch(<?php echo $match["game_id"]; ?>)' class="btn-secondary">Cancel</button>
                                    <?php endif; ?>
                                    <div style="font-size:14px;color:var(--muted);">Value: <?php echo $match["starter_value"]; ?></div>
                                <?php else : ?>
                                    <div style="font-weight:700;font-size:18px;">Completed</div>
                                    <div style="font-size:14px;color:var(--muted);">Winner: <?php echo $match["winner_id"] ? getName($match["winner_id"]) : 'TBD'; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="side-panel">
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <div class="player-card">
                        <img src="<?php echo $session?getUserThumbnail($session["user_id"]):'img/favicon.png'; ?>">
                        <div>
                            <div class="player-name"><?php echo $session?getName($session["user_id"]):'Guest'; ?></div>
                            <div style="font-size:13px;color:var(--muted);">Balance: <?php echo $session?getAllProfit($session["user_id"]):'0'; ?></div>
                        </div>
                    </div>

                    <h3 style="margin:6px 0 0 0;">Your Inventory</h3>
                    <div class="items-grid">
                        <?php if ($session) :
                            include_once "php/inventory_handler.php";
                            $inventory = getInventory($session["user_id"], false);
                            foreach ($inventory as $item) : ?>
                                <div id="card<?php echo $item["inventory_id"]; ?>" class="item-card" data-inv="<?php echo $item["inventory_id"]; ?>" data-val="<?php echo $item["item_value"]; ?>">
                                    <img src="<?php echo $item["item_image"]; ?>" alt="<?php echo htmlspecialchars($item["display_name"]); ?>">
                                    <div style="font-size:12px;margin-top:6px;"><?php echo $item["item_value"]; ?> Value</div>
                                    <button class="selectionbtn btn-secondary" style="margin-top:6px;">Select</button>
                                </div>
                            <?php endforeach;
                        else : ?>
                            <div>Please <button onclick="login()" class="btn-primary">login</button> to use inventory</div>
                        <?php endif; ?>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;">
                        <div>
                            <div style="color:var(--muted);">Selected Value</div>
                            <div id="valdiv">Value: 0</div>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end;">
                            <div style="display:flex;gap:8px;align-items:center;"><img id="side0" src="./img/gem.png" width="56" height="56" style="cursor:pointer;border-radius:8px;"/><img id="side1" src="./img/dog.png" width="56" height="56" style="cursor:pointer;border-radius:8px;"/></div>
                            <button id="okbtn" class="btn-primary" data-mode="create" disabled>Create / Post</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "layout/foot.php"; ?>
