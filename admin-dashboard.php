<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>
    <div class="layout">
  <aside class="sidebar">
    <div class="logo">
        <img src="images/placeholder_logo_1.png" alt="PhishAware Logo"> <!-- Replace with non-placeholder logo -->
        <span> | PhishAware</span>
    </div>
    <nav> <!-- Navigation links, map all of these to their respective pages -->
      <a href="#"><i class="fas fa-home"></i> | Admin Dashboard</a>
      <a href="#"><i class="fas fa-graduation-cap"></i> | Assign Training</a>
      <a href="#"><i class="fas fa-chart-line"></i> | User Results</a>
      <a href="#"><i class="fas fa-users"></i> | Users</a>
      <a href="#"><i class="fas fa-cog"></i> | Settings</a>
    </nav>
  </aside>
  <div class="main">
    <header class="topbar">
    <div class="topbar-left">
        <span>Welcome, [User Name]</span>

        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search users, training, reports..."> <!-- Implement a search functionality once available -->
        </div>
    </div>

    <div class="topbar-right">
        <button id="notifications-btn">
            <i class="fas fa-bell"></i>
        </button>

        <div class="profile-menu">
            <img src="images/" alt="Profile" class="avatar" id="profile-btn">

            <div class="dropdown hidden" id="profile-dropdown">
                <a href="#">My Profile</a>
                <a href="#">Settings</a>
                <a href="#">Log Out</a>
            </div>
        </div>
    </div>
</header>
    <section class="content">
      <div class="card-grid">
  <div class="card">
    <!-- Replace placeholder text with actual data, prefereably graphs or something visual. -->
    <h3>Completion Rate</h3>
    <p>0%</p>
  </div>
  <div class="card">
    <h3>Assigned Trainings</h3>
    <p>0</p>
  </div>
  <div class="card">
    <h3>Last Score</h3>
    <p>0%</p>
  </div>
    </div>
    </section>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/scripts.js"></script>
</body>
</html>