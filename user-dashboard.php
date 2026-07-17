<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
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
      <a href="#"><i class="fas fa-home"></i> | My Dashboard</a>
      <a href="#"><i class="fas fa-graduation-cap"></i> | Training</a>
      <a href="#"><i class="fas fa-chart-line"></i> | Results</a>
      <a href="#"><i class="fas fa-trophy"></i> | Badges</a>
      <a href="#"><i class="fas fa-cog"></i> | Settings</a>
    </nav>
  </aside>
  <div class="main">
    <header class="topbar">
      <div class="topbar-left">
        <span>Welcome, [User Name]</span> <!-- Replace [User Name] with the actual user's name via some PHP or JavaScript -->
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search your training, results, badges...">
        </div>
      </div>
            <div class="topbar-right">
            <button id="notifications-btn"><i class="fas fa-bell"></i></button>
            <div class="profile-menu">
                <img src="images/default_pfp.jpg" alt="Profile" class="avatar" id="profile-btn">
                    <div class="dropdown hidden" id="profile-dropdown">
                    <a href="#">My Profile</a>
                    <a href="#">Settings</a>
                    <a href="#">Log Out</a>
                    </div>
                </div>
    </header>
    <section class="content">
      <div class="card-grid"> <!-- All of these cards are for displaying user information and training progress, they are currently placeholders. ADD LOGIC TO POPULATE THEM WITH ACTUAL DATA!!! -->
  <div class="card">
    <h3>Assigned Trainings Completed</h3>
    <p>4/5</p>
  </div>

  <div class="card">
    <h3>Overdue Trainings</h3>
    <p>0</p>
  </div>

  <div class="card">
    <h3>Last Training Score</h3>
    <p>0%</p>
  </div>

  <div class="card">
    <h3>Current Training</h3>
    <p>In Progress</p>
  </div>

  <div class="card">
    <h3>Next Training</h3>
    <p>Not Assigned</p>
  </div>

  <div class="card">
    <h3>Phishing Awareness</h3>
    <p>80%</p>
  </div>

  <div class="card wide-card">
    <h3>Your Training Overview</h3>
    <div class="chart-container">
    <canvas id="overviewChart"></canvas>
    </div>

    
  </div>
    </div>
    </section>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/scripts.js"></script>
</body>
</html>