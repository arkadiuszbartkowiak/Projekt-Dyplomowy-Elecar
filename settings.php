<?php
  session_start();
  //Połączenie z bazą danych
  $mysqli = require __DIR__ . "/database.php";
  //Kontrola dostępu użytkownika 
  if(isset($_SESSION["user_id"]) == false) {
    header("Location: signin.php");
    exit;
  }
  $errordisplay = 0;
  $errormsg = "";
  // Zmiana hasła użytkownika
  if($_SERVER["REQUEST_METHOD"] === "POST") {
    $sql = sprintf("SELECT * FROM users WHERE email = '%s'", $_SESSION["user_email"]);
    $result = $mysqli->query($sql);
    $user = $result->fetch_assoc();

    if($user) { //Sprawdzenie czy stare i nowe hasło jest takie same
      if ($_POST["oldpassword"]==$_POST["newpassword"]){
          $errormsg = "Hasło nie może być takie same!";
          $errordisplay = 1;
      } //Sprawdzenie czy hasło zawiera odpowiednie znaki
      else if(!preg_match('@[A-Z]@', $_POST["newpassword"]) || !preg_match('@[a-z]@', $_POST["newpassword"]) || !preg_match('@[0-9]@', $_POST["newpassword"]) || !preg_match('@[^\w]@', $_POST["newpassword"]) || strlen($_POST["newpassword"]) < 8) {
        $errordisplay = 1;
        $errormsg = "Hasło musi zawierać przynajmniej 8 znaków, jedną literę wielką, jedną cyfrę oraz jeden znak specjalny.";
      } //Weryfikacja i zmiana hasła 
      else if(password_verify($_POST["oldpassword"], $user["password_hash"]) && $_POST["newpassword"] == $_POST["newpasswordrepeat"] && $_POST["oldpassword"]!=$_POST["newpassword"]) {
        $new_password_hash = password_hash($_POST["newpassword"], PASSWORD_DEFAULT);
        $email = $user["email"];
        $sql = "UPDATE users SET password_hash='$new_password_hash', ispasswordchanged=1 WHERE email='$email'";
        if (mysqli_query($mysqli, $sql)) {
          session_destroy();
          header("Location: index.php");
        } 
        else {
          $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
          $errordisplay = 1;
        }
        exit;
      }
      else {
        $errordisplay = 1;
        $errormsg = "Wprowadzone dane są niepoprawne.<br><b>Spróbuj ponownie.</b>";
      }
    }
  }
?>

<!DOCTYPE html>
<html lang="pl-PL">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Elecar - Ustawienia</title>
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
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6 text-black"><img class="me-2" src="images/logo.png" alt="" width="40" height="40">Witaj, <?php echo $_SESSION["user_firstname"].' '.$_SESSION["user_lastname"];?></a>
    <ul class="navbar-nav flex-row d-md-none">
      <li class="nav-item text-nowrap">
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
          <div class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 fs-6" href="index.php">
                  <i class="bi bi-house"></i>
                  Strona główna
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 fs-6" href="vehicles.php">
                  <i class="bi bi-truck"></i>
                  Pojazdy
                </a>
              </li>
              <?php if($_SESSION["user_role"] != 2) { ?>
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
                <a class="nav-link d-flex align-items-center gap-2 fs-6 active" aria-current="page" href="#">
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
          <h1 class="h2">Ustawienia</h1>
        </div>
        <?php if($_SESSION["user_ispasschanged"] == 0) { ?> 
          <div class="alert alert-danger alert-dismissible fade show mt-1" role="alert">
            Jest to pierwsze logowanie po stworzeniu konta. Wymagana jest zmiana hasła.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php } ?>
        <form method="post">
          <div class="mb-3">
            <label for="exampleInputPassword1" class="form-label">Stare hasło</label>
            <input type="password" name="oldpassword" class="form-control" id="exampleInputPassword1" required>
          </div>
          <div class="mb-3">
            <label for="exampleInputPassword1" class="form-label">Nowe hasło</label>
            <input type="password" name="newpassword" class="form-control" id="exampleInputPassword1" required>
          </div>
          <div class="mb-3">
            <label for="exampleInputPassword1" class="form-label">Powtórz nowe hasło</label>
            <input type="password" name="newpasswordrepeat" class="form-control" id="exampleInputPassword1" required>
          </div>
          <h5>Po zmianie hasła musisz się ponownie zalogować.</h5>
          <button type="submit" class="btn btn-primary">Zmień hasło</button>
        </form>
        <?php if($errordisplay == 1) { ?> 
          <div class="alert alert-danger alert-dismissible fade show mt-1" role="alert">
            <?php echo $errormsg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php } ?>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.2/dist/chart.umd.js" integrity="sha384-eI7PSr3L1XLISH8JdDII5YN/njoSsxfbrkCTnJrzXt+ENP5MOVBxD+l6sEG4zoLp" crossorigin="anonymous"></script><script src="dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>