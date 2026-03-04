<?php
require_once '../backend/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login_page.html');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DocSpace — Your Focused Workflow</title>
<link rel="stylesheet" href="../asset/style_main.css">

<style>
/* ---------------- Global Reset & Base ---------------- */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
html, body {
    height: 100%;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    color: #1f2937;
    background: #f5f7fa url('../images/background1.png') no-repeat center center fixed;
    background-size: cover;
}


/* ---------------- Header & Navbar ---------------- */
header.appbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    z-index: 1000;
}

.nav-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.app-logo {
    width: 46px;
    height: 46px;
    border-radius: 50%;
}
.app-name {
    font-size: 22px;
    font-weight: 700;
    color: #2e7d32;
}

.nav-center a {
    margin: 0 16px;
    text-decoration: none;
    color: #374151;
    font-weight: 500;
    transition: 0.2s ease;
}
.nav-center a:hover {
    color: #2e7d32;
}

.nav-right {
    position: relative;
}
.user-avatar {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    cursor: pointer;
    transition: transform 0.2s ease;
}
.user-avatar:hover { transform: scale(1.05); }

/* User dropdown menu */
.user-menu {
    position: absolute;
    right: 0;
    top: 60px;
    width: 220px;
    background: #fff;
    padding: 12px 16px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    display: none;
    flex-direction: column;
    gap: 8px;
    z-index: 1010;
}
.user-menu a {
    text-decoration: none;
    color: #374151;
    padding: 6px 8px;
    border-radius: 6px;
    transition: 0.2s ease;
}
.user-menu a:hover { background-color: #e0f2f1; color: #1b5e20; }
.user-menu .logout { color: #d32f2f; }

/* Welcome message */
.welcome-msg {
    margin-left: 280px; /* slightly right of sidebar */
    margin-top: 100px;
    font-size: 22px;
    font-weight: 500;
    color: #2e7d32;
    animation: fadeIn 1s ease forwards;
}
@keyframes fadeIn {
    from {opacity:0; transform:translateY(-10px);}
    to {opacity:1; transform:translateY(0);}
}

/* ---------------- Sidebar ---------------- */
.sidebar {
    width: 300px;
    max-height: calc(100vh - 90px);
    position: fixed;
    top: 90px;
    left: 0;
    background: linear-gradient(to bottom, #f0f8ff, #e8f5e9);
    padding: 20px;
    border-right: 1px solid #d0d0d0;
    border-bottom-right-radius: 16px;
    box-shadow: 3px 0 12px rgba(0,0,0,0.08);
    overflow-y: auto;
    transition: all 0.5s ease;
}
.sidebar.hidden { transform: translateX(-320px); opacity: 0; }

.sidebar::-webkit-scrollbar {
    width: 8px;
}
.sidebar::-webkit-scrollbar-thumb {
    background: #81c784;
    border-radius: 4px;
}

/* ---------------- Search Bar ---------------- */
.search-bar input {
    width: 100%;
    padding: 12px 16px;
    border-radius: 25px;
    border: 1px solid #81c784;
    outline: none;
    font-size: 14px;
    box-shadow: 0 2px 6px rgba(46, 125, 50, 0.15);
    transition: all 0.3s ease;
}
.search-bar input:focus {
    box-shadow: 0 4px 14px rgba(46, 125, 50, 0.3);
    border-color: #2e7d32;
}

/* ---------------- Category Cards ---------------- */
.category {
    background-color: #f9fff9;
    border-radius: 14px;
    border: 1px solid #c8e6c9;
    padding: 14px;
    margin-bottom: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
}
.category:hover { box-shadow: 0 6px 16px rgba(46,125,50,0.2); }

.category-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    color: #2e7d32;
}
.add-note {
    width: 28px; height: 28px;
    border-radius: 50%;
    font-size: 18px;
    border: none;
    cursor: pointer;
    background: linear-gradient(135deg, #4caf50, #388e3c);
    color: #fff;
    transition: all 0.25s ease;
}
.add-note:hover { background: linear-gradient(135deg, #81c784, #4caf50); transform: scale(1.1); }

/* ---------------- Notes List ---------------- */
.notes {
    list-style: none;
    padding-left: 10px;
    margin-top: 8px;
}
.notes li {
    padding: 8px 12px;
    font-size: 15px;
    color: #333;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    background-color: #ffffffcc;
    border-radius: 10px;
    margin-bottom: 6px;
    border-left: 4px solid transparent;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.25s ease;
}
.notes li:hover {
    background: linear-gradient(120deg, #c8facc, #a8e6a1);
    transform: translateY(-2px) scale(1.015);
    box-shadow: 0 6px 18px rgba(46,125,50,0.35);
    border-left: 4px solid #2e7d32;
}
.notes li.selected {
    background: linear-gradient(135deg, #66bb6a, #43a047);
    color: #fff;
    border-left: 4px solid #1b5e20;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(46,125,50,0.5);
    transform: scale(1.02);
    animation: pulseGlow 1.5s infinite alternate;
}
@keyframes pulseGlow {
    0% { box-shadow: 0 0 10px #4caf50, 0 0 20px rgba(76,175,80,0.35); }
    50% { box-shadow: 0 0 18px #4caf50, 0 0 28px rgba(76,175,80,0.4); }
    100% { box-shadow: 0 0 10px #4caf50, 0 0 20px rgba(76,175,80,0.35); }
}

/* ---------------- Add Category Input & Button ---------------- */
.add-category {
    display: flex;
    gap: 8px;
    align-items: center;
    border-top: 1px solid #e0e0e0;
    padding-top: 12px;
    margin-top: 20px;
}
.add-category input {
    flex: 1;
    padding: 10px 14px;
    border-radius: 22px;
    border: 1px solid #81c784;
    outline: none;
    font-size: 14px;
    box-shadow: 0 2px 6px rgba(46, 125, 50, 0.1);
    transition: all 0.3s ease;
}
.add-category input:focus {
    border-color: #2e7d32;
    box-shadow: 0 4px 12px rgba(46,125,50,0.25);
}
.add-category button {
    padding: 10px 18px;
    border: none;
    background: linear-gradient(135deg, #4caf50, #388e3c);
    color: #fff;
    font-weight: 600;
    border-radius: 22px;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.add-category button:hover { background: linear-gradient(135deg, #81c784, #4caf50); transform: scale(1.05); }

/* ---------------- Document Editor ---------------- */
.document-editor {
    margin-left: 320px;
    margin-right: 20px;
    margin-top: 40px;
    padding: 20px;
    border-radius: 12px;
    background-color: #f5f5f5;
    box-shadow: 0 4px 14px rgba(0,0,0,0.1);
}

/* ---------------- Control Buttons ---------------- */
.control-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 4px;
    font-size: 14px;
    color: #333;
}
.control-btn.rename { color: #1f6feb; }
.control-btn.delete { color: #d9534f; }
/* ---------------- Import Menu ---------------- */
.import-menu {
    position: relative;
}
.import-options {
    min-width: 150px;
    position: absolute;
    right: 8px;
    top: 34px;
    display: none;
    flex-direction: column;
    gap: 2px;
    padding: 8px;
    background: linear-gradient(180deg, #1bdaa1ff 0%, #fbfdff 100%);
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(12, 24, 40, 0.12);
    z-index: 200;
    transform-origin: top right;
    transform: scale(0.96);
    opacity: 0;
    transition: all 0.18s ease;
}
.import-menu.open .import-options { display: flex; opacity: 1; transform: scale(1); }

.import-options button {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 6px;
    font-size: 14px;
    border: none;
    border-radius: 6px;
    background: transparent;
    color: #ec2f0dff;
    cursor: pointer;
    transition: all 0.12s ease;
}
.import-options button:hover {
    background: rgba(74,144,226,0.08);
    color: #0b61d6;
    transform: translateX(2px);
}

.main-section {
    flex: 1; /* takes all remaining vertical space */
    display: flex; /* optional: for sidebar + content layout */
    background: #f5f7fa url('../images/background1.png') no-repeat center center fixed;
    background-size: cover;
    padding-top: 90px; /* leave space for fixed header */
}
/* ---------------- Footer ---------------- */
footer {
    background: #ffffff;
    color: #6b7280;
    padding: 20px 30px;
    text-align: center;
    font-size: 14px;
    border-top: 1px solid #e0e0e0;
    width: 100%;
}
footer .footer-section { margin: 6px 0; }
footer a { color: #2563eb; text-decoration: none; margin: 0 6px; font-weight: 500; }
footer a:hover { text-decoration: underline; }

/* ---------------- Responsive ---------------- */
@media (max-width: 768px) {
    .sidebar { width: 80%; }
    .document-editor { margin-left: 0; margin-top: 160px; }
    .welcome-msg { margin-left: 20px; }
}

</style>

<script src="../asset/script.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const userAvatar = document.getElementById('userAvatar');
    const userMenu = document.getElementById('userMenu');
    const toggleBtn = document.getElementById('toggle-sidebar');
    const sidebar = document.getElementById('sidebar1');

    // User menu toggle
    userAvatar.addEventListener('click', () => {
        userMenu.style.display = userMenu.style.display === 'flex' ? 'none' : 'flex';
    });

    // Sidebar toggle
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
        });
    }
});
</script>
</head>

<body>
<header class="appbar">
  <div class="nav-left">
    <img src="../asset/images/log.png" class="app-logo" />
    <span class="app-name">DocSpace</span>
  </div>

  <nav class="nav-center">
    <a href="main_page.php">Home</a>
    <a href="help.html">help</a>
    <a href="contactUs.html">contact us</a>

  </nav>

  <div class="nav-right">
    <img src="../asset/images/user.png" class="user-avatar" id="userAvatar" />
    <div class="user-menu" id="userMenu">
      <p><strong><?= htmlspecialchars($user['name']) ?></strong></p>
      <small><?= htmlspecialchars($user['email']) ?></small>
      <hr />
      <a href="#">Settings</a>
      <a href="login_page.html" class="logout">Logout</a>
    </div>
  </div>
</header>

<!-- Optional toggle button for sidebar -->
<button id="toggle-sidebar" style="position: fixed; top: 100px; left: 310px; z-index:1001; padding:8px 12px; background:#2e7d32; color:#fff; border:none; border-radius:6px; cursor:pointer;">My Douments</button>
<div class="main-content">
<aside id="sidebar1" class="sidebar">
    <div class="search-bar">
        <input type="text" placeholder="Search documents..." id="search-docs">
    </div>
    <div id="category-container" class="categories">
        <div id="category" class="category">
            <div class="category-header">
                <span id="categoryName"></span>
                <div class="category-actions">
                    <button id="add-doc" class="add-note">+</button>
                </div>
            </div>
            <ul id="documents" class="notes"></ul>
        </div>
    </div>
    <div class="add-category">
        <input type="text" id="NewCategoryName" placeholder="New category name">
        <button id="add-category">Add</button>
    </div>
</aside>
</div>
<section class="main-section">
<div class="welcome-msg">
    Welcome, <?= htmlspecialchars($user['name']) ?>! This is your distraction-free, ad-free workspace.
</div>

<div class="document-editor">
    <div class="editor-header">
        <h2 id="doc-title" contenteditable="true">Document Title</h2>
        <div class="editor-controls" style="display:flex; gap:8px; align-items:center;"></div>
    </div>
</div>
</section>
<footer>
    <div class="footer-section">
        <strong>DocSpace</strong> — Your focused workflow, designed for students and deep thinkers.
    </div>
    <div class="footer-section">
        <a href="#">Help</a>
    </div>
    <div class="footer-section">
        © 2026 DocSpace · Crafted for learning and productivity
    </div>
</footer>

</body>
</html>
