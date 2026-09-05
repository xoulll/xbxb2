<?php
    // Start session and load configs
    session_start();
   include_once dirname(__FILE__) . "/php/config.php";
include_once dirname(__FILE__) . "/php/functions.php";
include_once dirname(__FILE__) . "/php/inventory_handler.php";
    
    $session = isset($_SESSION['user_id']) ? array(
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'thumbnail' => getUserThumbnail($_SESSION['user_id'])
    ) : null;

    // Get games for display
    $games = [];
    if ($session) {
        $games = getGames($session['user_id']);
    } else {
        $games = getGames("NULL");
    }
    
    // Calculate stats
    $totalGames = count($games);
    $totalValue = 0;
    $openGames = 0;
    
    foreach ($games as $game) {
        $totalValue += (int)$game['starter_value'];
        if (!$game['end_date']) $openGames++;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AmpDuel - Coinflip</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/sweetalert2.all.min.js"></script>
    
    <!-- Styles -->
    <link rel="stylesheet" href="css/sweetalert2-dark.css">
    <style>
        :root {
            --primary: #0ea5e9;
            --primary-dark: #0284c7;
            --primary-light: #06b6d4;
            --bg-0: #0f172a;
            --bg-1: #1e293b;
            --bg-2: #334155;
            --text: #f1f5f9;
            --text-muted: #cbd5e1;
            --accent: #ec4899;
            --accent-alt: #f97316;
            --border: rgba(148, 163, 184, 0.1);
            --glass: rgba(15, 23, 42, 0.6);
            --glass-border: rgba(148, 163, 184, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--bg-0) 0%, #1a1f35 100%);
            color: var(--text);
            overflow: hidden;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        /* ==================== SIDEBAR ==================== */
        .sidebar {
            width: 240px;
            background: var(--glass);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-header {
            padding: 16px 12px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .chat-input {
            width: 100%;
            background: var(--bg-1);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 8px 10px;
            border-radius: 6px;
            font-size: 12px;
        }

        .chat-input::placeholder {
            color: var(--text-muted);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 12px 8px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .chat-message {
            background: var(--bg-1);
            border: 1px solid var(--border);
            padding: 10px;
            border-radius: 6px;
            font-size: 12px;
        }

        .chat-user {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .chat-text {
            color: var(--text);
            word-break: break-word;
        }

        /* ==================== MAIN CONTENT ==================== */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* ==================== HEADER ==================== */
        .header {
            background: var(--glass);
            border-bottom: 1px solid var(--border);
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-tabs {
            display: flex;
            gap: 16px;
        }

        .nav-tab {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            padding: 4px 0;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }

        .nav-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .nav-tab:hover {
            color: var(--text);
        }

        .header-right {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, var(--primary-dark) 0%, #0162b0 100%);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--bg-1);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--bg-2);
            border-color: var(--text-muted);
        }

        .profile-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-1);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }

        .profile-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--primary);
        }

        /* ==================== STATS BAR ==================== */
        .stats-bar {
            display: flex;
            gap: 24px;
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
        }

        .stat-item {
            display: flex;
            flex-direction: column;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* ==================== GAMES GRID ==================== */
        .games-container {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
        }

        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        .game-card {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(30, 41, 59, 0.6) 100%);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            transition: all 0.3s;
            cursor: pointer;
        }

        .game-card:hover {
            border-color: var(--primary);
            box-shadow: 0 8px 24px rgba(14, 165, 233, 0.1);
            transform: translateY(-4px);
        }

        .game-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .game-value {
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
        }

        .game-status {
            font-size: 11px;
            color: var(--text-muted);
            background: var(--bg-1);
            padding: 4px 8px;
            border-radius: 4px;
        }

        .game-status.open {
            color: #10b981;
            background: rgba(16, 185, 129, 0.1);
        }

        .game-status.completed {
            color: var(--accent);
            background: rgba(236, 72, 153, 0.1);
        }

        .players-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding: 12px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .player-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            flex: 1;
        }

        .player-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            overflow: hidden;
        }

        .player-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .player-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .vs-divider {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0 8px;
        }

        .items-display {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            margin-top: 12px;
        }

        .item-thumb {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            background: var(--bg-1);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
        }

        .item-thumb img {
            width: 100%;
            height: 100%;
            border-radius: 4px;
            object-fit: cover;
        }

        .game-footer {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .game-footer button {
            flex: 1;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: 600;
        }

        /* ==================== MODALS ==================== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: var(--glass);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 24px;
            cursor: pointer;
        }

        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .inventory-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 12px;
            background: var(--bg-1);
            border: 2px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .inventory-item.selected {
            border-color: var(--primary);
            background: rgba(14, 165, 233, 0.1);
        }

        .inventory-item-img {
            width: 60px;
            height: 60px;
            border-radius: 6px;
            background: var(--bg-0);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .inventory-item-img img {
            width: 100%;
            height: 100%;
            border-radius: 4px;
            object-fit: cover;
        }

        .inventory-item-value {
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
        }

        .side-selector {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            justify-content: center;
        }

        .side-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 12px;
            background: var(--bg-1);
            border: 2px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .side-option.selected {
            border-color: var(--primary);
            background: rgba(14, 165, 233, 0.1);
        }

        .side-option img {
            width: 80px;
            height: 80px;
        }

        .side-option-label {
            font-size: 13px;
            font-weight: 600;
        }

        .selected-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-1);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
        }

        .selected-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
        }

        .modal-actions {
            display: flex;
            gap: 12px;
        }

        .modal-actions button {
            flex: 1;
            padding: 12px;
            font-size: 13px;
            font-weight: 600;
        }

        /* ==================== LOADING SPINNER ==================== */
        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(148, 163, 184, 0.1);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ==================== SCROLLBAR ==================== */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(14, 165, 233, 0.3);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(14, 165, 233, 0.6);
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 1024px) {
            .sidebar {
                width: 200px;
            }

            .games-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: 200px;
                border-right: none;
                border-bottom: 1px solid var(--border);
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .header-left {
                gap: 16px;
            }

            .games-grid {
                grid-template-columns: 1fr;
            }

            .inventory-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- SIDEBAR / CHAT -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">Chat</div>
                <input type="text" id="chatInput" class="chat-input" placeholder="Say something...">
            </div>
            <div class="chat-messages" id="chatMessages">
                <div class="chat-message">
                    <div class="chat-user">System</div>
                    <div class="chat-text">Welcome to AmpDuel Coinflip!</div>
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- HEADER -->
            <div class="header">
                <div class="header-left">
                    <div class="logo">AMPDUEL</div>
                    <div class="nav-tabs">
                        <button class="nav-tab active">Coinflip</button>
                        <button class="nav-tab">Leaderboard</button>
                    </div>
                </div>
                <div class="header-right">
                    <?php if ($session): ?>
                        <button class="btn btn-primary" onclick="openCreateModal()">Create Match</button>
                        <button class="profile-btn" onclick="openProfileModal()">
                            <img src="<?php echo $session['thumbnail']; ?>" class="profile-avatar">
                            <span><?php echo htmlspecialchars($session['username']); ?></span>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-primary" onclick="login()">Login</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- STATS BAR -->
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $totalGames; ?></div>
                    <div class="stat-label">All Games</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo number_format($totalValue, 1); ?></div>
                    <div class="stat-label">Total Value</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $openGames; ?></div>
                    <div class="stat-label">Open Games</div>
                </div>
            </div>

            <!-- GAMES GRID -->
            <div class="games-container">
                <div class="games-grid" id="gamesGrid">
                    <?php foreach ($games as $game): 
                        $starterName = getName($game['starter_id']);
                        $playerName = $game['player_id'] ? getName($game['player_id']) : 'Waiting...';
                        $starterItems = json_decode($game['starter_items'], true) ?? [];
                        $playerItems = json_decode($game['player_items'], true) ?? [];
                        $isOpen = !$game['end_date'];
                    ?>
                    <div class="game-card" id="game-<?php echo $game['game_id']; ?>">
                        <div class="game-header">
                            <div class="game-value">
                                Value: <?php echo $game['starter_value']; ?>
                            </div>
                            <div class="game-status <?php echo $isOpen ? 'open' : 'completed'; ?>">
                                <?php echo $isOpen ? 'Open' : 'Completed'; ?>
                            </div>
                        </div>

                        <div class="players-section">
                            <div class="player-info">
                                <div class="player-avatar">
                                    <img src="<?php echo getUserThumbnail($game['starter_id']); ?>" alt="<?php echo htmlspecialchars($starterName); ?>">
                                </div>
                                <div class="player-name"><?php echo htmlspecialchars($starterName); ?></div>
                            </div>
                            
                            <div class="vs-divider">vs</div>
                            
                            <div class="player-info">
                                <div class="player-avatar">
                                    <?php if ($game['player_id']): ?>
                                        <img src="<?php echo getUserThumbnail($game['player_id']); ?>" alt="<?php echo htmlspecialchars($playerName); ?>">
                                    <?php else: ?>
                                        <span>?</span>
                                    <?php endif; ?>
                                </div>
                                <div class="player-name"><?php echo htmlspecialchars($playerName); ?></div>
                            </div>
                        </div>

                        <div class="items-display">
                            <?php foreach ($starterItems as $item): 
                                $itemInfo = getItemInfo($item['item_id']);
                            ?>
                                <div class="item-thumb">
                                    <img src="<?php echo $itemInfo['item_image']; ?>" alt="<?php echo htmlspecialchars($itemInfo['display_name']); ?>">
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (!$isOpen): foreach ($playerItems as $item): 
                                $itemInfo = getItemInfo($item['item_id']);
                            ?>
                                <div class="item-thumb">
                                    <img src="<?php echo $itemInfo['item_image']; ?>" alt="<?php echo htmlspecialchars($itemInfo['display_name']); ?>">
                                </div>
                            <?php endforeach; endif; ?>
                        </div>

                        <div class="game-footer">
                            <?php if ($isOpen): ?>
                                <?php if (!$session): ?>
                                    <button class="btn btn-primary" onclick="login()">Join</button>
                                <?php elseif ($game['starter_id'] != $session['user_id']): ?>
                                    <button class="btn btn-primary" onclick="openJoinModal(<?php echo $game['game_id']; ?>, <?php echo $game['starter_value']; ?>)">Join</button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" onclick="cancelMatch(<?php echo $game['game_id']; ?>)">Cancel</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <div style="width: 100%; text-align: center; color: var(--text-muted); font-size: 12px;">
                                    Winner: <?php echo getName($game['winner_id']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- CREATE MATCH MODAL -->
    <div class="modal-overlay" id="createModal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Create Coinflip Match</h2>
                <button class="modal-close" onclick="closeModal('createModal')">×</button>
            </div>

            <h3 style="font-size: 14px; margin-bottom: 12px; color: var(--text-muted);">Select Items</h3>
            <div class="inventory-grid" id="createInventoryGrid">
                <!-- Populated by JS -->
            </div>

            <h3 style="font-size: 14px; margin-bottom: 12px; color: var(--text-muted);">Choose Your Side</h3>
            <div class="side-selector" id="sideSelector">
                <div class="side-option" data-side="0" onclick="selectSide(0)">
                    <img src="./img/gem.png" alt="Red">
                    <div class="side-option-label">Red</div>
                </div>
                <div class="side-option" data-side="1" onclick="selectSide(1)">
                    <img src="./img/dog.png" alt="Blue">
                    <div class="side-option-label">Blue</div>
                </div>
            </div>

            <div class="selected-info">
                <div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Total Value</div>
                    <div class="selected-value" id="createValueDisplay">0</div>
                </div>
                <button class="btn btn-secondary" onclick="closeModal('createModal')">Clear</button>
            </div>

            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModal('createModal')">Cancel</button>
                <button class="btn btn-primary" id="createConfirmBtn" disabled onclick="confirmCreateMatch()">Create Match</button>
            </div>
        </div>
    </div>

    <!-- JOIN MATCH MODAL -->
    <div class="modal-overlay" id="joinModal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Join Coinflip Match</h2>
                <button class="modal-close" onclick="closeModal('joinModal')">×</button>
            </div>

            <p style="color: var(--text-muted); margin-bottom: 16px; font-size: 13px;">
                Select items with a value between <span id="joinValueRange" style="color: var(--primary); font-weight: 600;"></span>
            </p>

            <h3 style="font-size: 14px; margin-bottom: 12px; color: var(--text-muted);">Select Items</h3>
            <div class="inventory-grid" id="joinInventoryGrid">
                <!-- Populated by JS -->
            </div>

            <div class="selected-info">
                <div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Total Value</div>
                    <div class="selected-value" id="joinValueDisplay">0</div>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModal('joinModal')">Cancel</button>
                <button class="btn btn-primary" id="joinConfirmBtn" disabled onclick="confirmJoinMatch()">Join Match</button>
            </div>
        </div>
    </div>

    <!-- PROFILE MODAL -->
    <div class="modal-overlay" id="profileModal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Profile</h2>
                <button class="modal-close" onclick="closeModal('profileModal')">×</button>
            </div>

            <?php if ($session): ?>
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border);">
                    <img src="<?php echo $session['thumbnail']; ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <div style="font-size: 16px; font-weight: 700; margin-bottom: 8px;"><?php echo htmlspecialchars($session['username']); ?></div>
                        <div style="color: var(--text-muted); font-size: 13px; margin-bottom: 4px;">Balance</div>
                        <div style="font-size: 20px; font-weight: 700; color: var(--primary);"><?php echo getAllProfit($session['user_id']); ?></div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeModal('profileModal'); logout()">Logout</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // ==================== STATE MANAGEMENT ====================
        let gameState = {
            selectedItems: [],
            selectedSide: null,
            selectedGameId: null,
            minValue: 0,
            maxValue: 0,
            currentValue: 0
        };

        let inventory = <?php echo json_encode(isset($_SESSION['user_id']) ? getInventory($_SESSION['user_id'], false) : []); ?>;

        // ==================== MODAL FUNCTIONS ====================
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
            resetGameState();
        }

        function resetGameState() {
            gameState.selectedItems = [];
            gameState.selectedSide = null;
            gameState.selectedGameId = null;
            gameState.minValue = 0;
            gameState.maxValue = 0;
            gameState.currentValue = 0;
            updateUIValues();
            document.querySelectorAll('.inventory-item, .side-option').forEach(el => {
                el.classList.remove('selected');
            });
        }

        // ==================== CREATE MATCH ====================
        function openCreateModal() {
            resetGameState();
            gameState.minValue = 10;
            populateInventoryGrid('createInventoryGrid');
            openModal('createModal');
        }

        function populateInventoryGrid(gridId) {
            const grid = document.getElementById(gridId);
            grid.innerHTML = '';
            
            if (!inventory.length) {
                grid.innerHTML = '<p style="color: var(--text-muted);">No items in inventory</p>';
                return;
            }

            inventory.forEach(item => {
                const div = document.createElement('div');
                div.className = 'inventory-item';
                div.onclick = () => toggleItemSelection(item, gridId);
                div.innerHTML = `
                    <div class="inventory-item-img">
                        <img src="${item.item_image}" alt="${item.display_name}">
                    </div>
                    <div class="inventory-item-value">${item.item_value}v</div>
                `;
                grid.appendChild(div);
            });
        }

        function toggleItemSelection(item, gridId) {
            const idx = gameState.selectedItems.findIndex(i => i.inventory_id === item.inventory_id);
            
            if (idx === -1) {
                gameState.selectedItems.push(item);
                gameState.currentValue += parseInt(item.item_value);
            } else {
                gameState.selectedItems.splice(idx, 1);
                gameState.currentValue -= parseInt(item.item_value);
            }

            updateUIValues();
            updateInventoryUI(gridId);
        }

        function updateInventoryUI(gridId) {
            const grid = document.getElementById(gridId);
            const items = grid.querySelectorAll('.inventory-item');
            items.forEach(el => {
                const index = Array.from(items).indexOf(el);
                if (gameState.selectedItems.find(i => i.inventory_id === inventory[index].inventory_id)) {
                    el.classList.add('selected');
                } else {
                    el.classList.remove('selected');
                }
            });
        }

        function selectSide(side) {
            gameState.selectedSide = side;
            document.querySelectorAll('.side-option').forEach(el => {
                el.classList.remove('selected');
            });
            document.querySelector(`[data-side="${side}"]`).classList.add('selected');
            updateUIValues();
        }

        function updateUIValues() {
            const isValid = gameState.selectedItems.length > 0 && gameState.selectedSide !== null && gameState.currentValue >= gameState.minValue;
            
            document.getElementById('createValueDisplay').textContent = gameState.currentValue;
            document.getElementById('joinValueDisplay').textContent = gameState.currentValue;
            document.getElementById('createConfirmBtn').disabled = !isValid;
            document.getElementById('joinConfirmBtn').disabled = !isValid || (gameState.maxValue > 0 && gameState.currentValue > gameState.maxValue);
        }

        function confirmCreateMatch() {
            if (gameState.selectedItems.length === 0 || gameState.selectedSide === null) {
                Swal.fire('Error', 'Select items and a side', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Match',
                text: `Create a ${gameState.currentValue} value match?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Create',
                confirmButtonColor: '#0ea5e9'
            }).then(result => {
                if (result.isConfirmed) {
                    postCreateMatch();
                }
            });
        }

        function postCreateMatch() {
            const itemIds = gameState.selectedItems.map(i => i.inventory_id);
            
            Swal.fire({ allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: './php/game.php',
                type: 'POST',
                data: {
                    type: 'create',
                    side: gameState.selectedSide,
                    item_ids: JSON.stringify(itemIds)
                },
                success: function(res) {
                    Swal.close();
                    try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) {}
                    
                    if (!res || res.Error) {
                        Swal.fire('Error', res?.Error || 'Failed to create match', 'error');
                    } else {
                        Swal.fire('Success', 'Match created!', 'success').then(() => {
                            window.location.reload();
                        });
                    }
                },
                error: () => {
                    Swal.close();
                    Swal.fire('Error', 'Network error', 'error');
                }
            });
        }

        // ==================== JOIN MATCH ====================
        function openJoinModal(gameId, matchValue) {
            resetGameState();
            gameState.selectedGameId = gameId;
            gameState.minValue = Math.max(10, matchValue - 10);
            gameState.maxValue = matchValue + 10;
            
            document.getElementById('joinValueRange').textContent = `${gameState.minValue} - ${gameState.maxValue}`;
            populateInventoryGrid('joinInventoryGrid');
            openModal('joinModal');
        }

        function confirmJoinMatch() {
            if (gameState.selectedItems.length === 0) {
                Swal.fire('Error', 'Select items', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Join',
                text: `Join with ${gameState.currentValue} value?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Join',
                confirmButtonColor: '#0ea5e9'
            }).then(result => {
                if (result.isConfirmed) {
                    postJoinMatch();
                }
            });
        }

        function postJoinMatch() {
            const itemIds = gameState.selectedItems.map(i => i.inventory_id);
            
            Swal.fire({ allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: './php/game.php',
                type: 'POST',
                data: {
                    type: 'play',
                    game_id: gameState.selectedGameId,
                    item_ids: JSON.stringify(itemIds)
                },
                success: function(res) {
                    Swal.close();
                    try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) {}
                    
                    if (!res || res.Error) {
                        Swal.fire('Error', res?.Error || 'Failed to join', 'error');
                    } else {
                        Swal.fire('Success', 'Joined match!', 'success').then(() => {
                            window.location.reload();
                        });
                    }
                },
                error: () => {
                    Swal.close();
                    Swal.fire('Error', 'Network error', 'error');
                }
            });
        }

        // ==================== CANCEL MATCH ====================
        function cancelMatch(gameId) {
            Swal.fire({
                title: 'Cancel Match',
                text: 'Are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, cancel',
                confirmButtonColor: '#0ea5e9'
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire({ allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });

                    $.ajax({
                        url: './php/game.php',
                        type: 'POST',
                        data: { type: 'cancel', game_id: gameId },
                        success: function(res) {
                            Swal.close();
                            try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch(e) {}
                            
                            if (!res || res.Error) {
                                Swal.fire('Error', res?.Error || 'Failed to cancel', 'error');
                            } else {
                                Swal.fire('Success', 'Match cancelled', 'success').then(() => {
                                    document.getElementById(`game-${gameId}`).remove();
                                });
                            }
                        },
                        error: () => {
                            Swal.close();
                            Swal.fire('Error', 'Network error', 'error');
                        }
                    });
                }
            });
        }

        // ==================== CHAT ====================
        document.getElementById('chatInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && this.value.trim()) {
                sendChatMessage(this.value.trim());
                this.value = '';
            }
        });

        function sendChatMessage(message) {
            const messagesContainer = document.getElementById('chatMessages');
            const messageEl = document.createElement('div');
            messageEl.className = 'chat-message';
            messageEl.innerHTML = `
                <div class="chat-user">You</div>
                <div class="chat-text">${escapeHtml(message)}</div>
            `;
            messagesContainer.appendChild(messageEl);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // ==================== AUTH ====================
        function login() {
            Swal.fire('Info', 'Login not implemented yet', 'info');
        }

        function logout() {
            window.location.href = './php/logout.php';
        }

        function openProfileModal() {
            openModal('profileModal');
        }

        // ==================== INIT ====================
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) closeModal(this.id);
            });
        });
    </script>
</body>
</html>
