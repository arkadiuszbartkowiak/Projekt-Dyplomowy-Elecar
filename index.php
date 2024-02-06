<?php
  session_start();
  $mysqli = require __DIR__ . "/database.php";
  if(isset($_SESSION["user_id"]) == false) {
    header("Location: signin.php");
    exit;
  }
  else if ($_SESSION["user_ispasschanged"] == 0) {
    header("Location: settings.php");
    exit;
  }
?>
<!DOCTYPE html>
<html lang="pl-PL">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Elecar - Strona Główna</title>
  <link rel="icon" type="image/x-icon" href="/images/logo.png">
  <link rel="stylesheet" type="text/css" href="styles/dashboard.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    .nav-scroller {
      position: relative;
      z-index: 2;
      height: 2.75rem;
      overflow-y: hidden;
    }
    
    .nav-scroller .nav {
      display: flex;
      flex-wrap: nowrap;
      padding-bottom: 1rem;
      margin-top: -1px;
      overflow-x: auto;
      text-align: center;
      white-space: nowrap;
      -webkit-overflow-scrolling: touch;
    }
  </style>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body> 
  <header class="navbar sticky-top flex-md-nowrap p-0 shadow" data-bs-theme="dark">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6 text-black">
      <!-- Wstawienie logo strony -->
      <img class="me-2" src="images/logo.png" alt="" width="40" height="40"> 
      <!-- Wyświetlenie aktualnie zalogowanego użytkownika -->
      Witaj, <?php echo $_SESSION["user_firstname"].' '.$_SESSION["user_lastname"];?>
    </a>
    <ul class="navbar-nav flex-row d-md-none">
      <li class="nav-item text-nowrap">
        <!-- Przycisk do rozwinięcia lewego panelu -->
        <button id="navbtn" class="nav-link px-3 text-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
          <i class="bi bi-list"></i> 
        </button>
      </li>
    </ul>
  </header>
  <div class="container-fluid">
    <div class="row">
      <div class="sidebar border border-right col-md-3 col-lg-2 p-0 bg-body-tertiary">
        <div class="offcanvas-md offcanvas-end bg-body-tertiary" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
          <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarMenuLabel">Elecar</h5> 
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
          </div>
          <!-- Przycisk strony głównej -->
          <div class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 fs-6 active" aria-current="page" href="#">
                  <i class="bi bi-house"></i>
                  Strona główna
                </a>
              </li>
              <!-- Przycisk podstrony 'Pojazdy' -->
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 fs-6" href="vehicles.php">
                  <i class="bi bi-truck"></i>
                  Pojazdy
                </a>
              </li>
              <!-- Sprawdzenie aktualnie zalogowanego użytkownika -->
              <?php if($_SESSION["user_role"] != 2) { ?>
              <!-- Przycisk podstrony 'Wypożyczenia' -->
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 fs-6" href="reservations.php">
                  <i class="bi bi-cart3"></i>
                  Wypożyczenia
                </a>
              </li>
              <?php } ?>
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 fs-6" href="service.php">
                  <i class="bi bi-tools"></i>
                  Serwis
                </a>
              </li>
              <?php if($_SESSION["user_role"] != 2) { ?>
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 fs-6" href="clients.php">
                  <i class="bi bi-people-fill"></i>
                  <?php if($_SESSION["user_role"] == 1) { ?>Użytkownicy / Klienci<?php } ?>
                  <?php if($_SESSION["user_role"] != 1) { ?>Klienci<?php } ?> 
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 fs-6" href="reports.php">
                  <i class="bi bi-bar-chart-line"></i>
                  Raporty
                </a>
              </li>
              <?php } ?>
            </ul>
            <hr class="my-3">
            <ul class="nav flex-column mb-auto">
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 fs-6" href="settings.php">
                  <i class="bi bi-gear"></i>
                  Ustawienia
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 fs-6" href="logout.php">
                  <i class="bi bi-door-open"></i>
                  Wyloguj się
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2">Strona Główna - Statystyki</h1>
        </div>
        <?php 
          $sql = sprintf("SELECT * FROM vehicles");
          $result = $mysqli->query($sql);
          $rowcount = mysqli_num_rows( $result );
          $sql2 = sprintf("SELECT * FROM vehicles where status='Wypożyczony'");
          $result2 = $mysqli->query($sql2);
          $rowcount2 = mysqli_num_rows( $result2 );
          $sql3 = sprintf("SELECT * FROM vehicles where maintenance_needed='1'");
          $result3 = $mysqli->query($sql3);
          $rowcount3 = mysqli_num_rows( $result3 );
          $sql4 = sprintf("SELECT * FROM vehicles where status='W serwisie'");
          $result4 = $mysqli->query($sql4);
          $rowcount4 = mysqli_num_rows( $result4 );
          $sql5 = sprintf("SELECT * FROM `vehicles` WHERE status='Dostępny' AND maintenance_needed='0'");
          $result5 = $mysqli->query($sql5);
          $rowcount5 = mysqli_num_rows( $result5 );
          $sql6 = sprintf("SELECT * FROM vehicles where typ='Hulajnoga' AND status='Dostępny' AND maintenance_needed='0'");
          $result6 = $mysqli->query($sql6);
          $rowcount6 = mysqli_num_rows( $result6 );
          $sql7 = sprintf("SELECT * FROM vehicles WHERE typ='Auto'");
          $result7 = $mysqli->query($sql7);
          $rowcount7 = mysqli_num_rows( $result7 );
          $sql8 = sprintf("SELECT * FROM vehicles WHERE typ='Auto' AND status='Wypożyczony'");
          $result8 = $mysqli->query($sql8);
          $rowcount8 = mysqli_num_rows( $result8 );
          $sql9 = sprintf("SELECT * FROM vehicles WHERE typ='Auto' AND maintenance_needed='1'");
          $result9 = $mysqli->query($sql9);
          $rowcount9 = mysqli_num_rows( $result9 );
          $sql10 = sprintf("SELECT * FROM vehicles WHERE typ='Auto' AND status='W serwisie'");
          $result10 = $mysqli->query($sql10);
          $rowcount10 = mysqli_num_rows( $result10 );
          $sql11 = sprintf("SELECT * FROM vehicles WHERE typ='Auto' AND status='Dostępny' AND maintenance_needed='0'");
          $result11 = $mysqli->query($sql11);
          $rowcount11 = mysqli_num_rows( $result11 );
          $sql12 = sprintf("SELECT * FROM vehicles WHERE typ='Hulajnoga'");
          $result12 = $mysqli->query($sql12);
          $rowcount12 = mysqli_num_rows( $result12 );
          $sql13 = sprintf("SELECT * FROM vehicles WHERE typ='Hulajnoga' AND status='Wypożyczony'");
          $result13 = $mysqli->query($sql13);
          $rowcount13 = mysqli_num_rows( $result13 );
          $sql14 = sprintf("SELECT * FROM vehicles WHERE typ='Hulajnoga' AND maintenance_needed='1'");
          $result14 = $mysqli->query($sql14);
          $rowcount14 = mysqli_num_rows( $result14 );
          $sql15 = sprintf("SELECT * FROM vehicles WHERE typ='Hulajnoga' AND status='W serwisie'");
          $result15 = $mysqli->query($sql15);
          $rowcount15 = mysqli_num_rows( $result15 );
          $sql16 = sprintf("SELECT * FROM vehicles WHERE typ='Hulajnoga' AND status='Dostępny' AND maintenance_needed='0'");
          $result16 = $mysqli->query($sql16);
          $rowcount16 = mysqli_num_rows( $result16 );
        ?>
        <div class="container-sm text-center align-content-center">
          <h1 class="me-3 mb-3 pt-1">POJAZDY</h1>
          <div class="row border border-2 border-dark me-3 mb-3 rounded pt-1">
            <h1><i class="bi bi-car-front h2"></i>  <i class="bi bi-scooter h2"></i></h1>
            <table class="table table-borderless">
              <thead>
                <tr>
                  <th scope="col">WSZYSTKIE</th>
                  <th scope="col">ZAREZERWOWANE</th>
                  <th scope="col">DOSTĘPNE DO REZERWACJI</th>
                  <th scope="col">DO SERWISU</th>
                  <th scope="col">AKTUALNIE SERWISOWANE</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><h2><?php echo $rowcount; ?></h2></td>
                  <td><h2><?php echo $rowcount2; ?></h2></td>
                  <td><h2><?php echo $rowcount5; ?></h2></td>
                  <td><h2><?php echo $rowcount3; ?></h2></td>
                  <td><h2><?php echo $rowcount4; ?></h2></td>
                </tr>
              </tbody>
            </table>
          </div>
          <h1 class="me-3 mb-3 pt-1">AUTA</h1>
          <div class="row border border-2 border-dark me-3  mb-3 rounded pt-1">
            <i class="bi bi-car-front h2"></i>
            <table class="table table-borderless">
              <thead>
                <tr>
                  <th scope="col">WSZYSTKIE</th>
                  <th scope="col">ZAREZERWOWANE</th>
                  <th scope="col">DOSTĘPNE DO REZERWACJI</th>
                  <th scope="col">DO SERWISU</th>
                  <th scope="col">AKTUALNIE SERWISOWANE</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><h2><?php echo $rowcount7; ?></h2></td>
                  <td><h2><?php echo $rowcount8; ?></h2></td>
                  <td><h2><?php echo $rowcount11; ?></h2></td>
                  <td><h2><?php echo $rowcount9; ?></h2></td>
                  <td><h2><?php echo $rowcount10; ?></h2></td>
                </tr>
              </tbody>
            </table>
          </div>
          <h1 class="me-3 mb-3 pt-1">HULAJNOGI</h1>
          <div class="row border border-2 border-dark me-3  mb-3 rounded pt-1">
            <i class="bi bi-scooter h2"></i>
            <table class="table table-borderless">
              <thead>
                <tr>
                  <th scope="col">WSZYSTKIE</th>
                  <th scope="col">ZAREZERWOWANE</th>
                  <th scope="col">DOSTĘPNE DO REZERWACJI</th>
                  <th scope="col">DO SERWISU</th>
                  <th scope="col">AKTUALNIE SERWISOWANE</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><h2><?php echo $rowcount12; ?></h2></td>
                  <td><h2><?php echo $rowcount13; ?></h2></td>
                  <td><h2><?php echo $rowcount16; ?></h2></td>
                  <td><h2><?php echo $rowcount14; ?></h2></td>
                  <td><h2><?php echo $rowcount15; ?></h2></td>  
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.2/dist/chart.umd.js" integrity="sha384-eI7PSr3L1XLISH8JdDII5YN/njoSsxfbrkCTnJrzXt+ENP5MOVBxD+l6sEG4zoLp" crossorigin="anonymous"></script><script src="dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>