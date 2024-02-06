<?php  
  
  session_start();
  //Połączenie z bazą danych
  $mysqli = require __DIR__ . "/database.php";
  $uploadOk = 1;
  $ImgError = "Wystąpiły nastepujące problemy podczas importowania zdjęcia:<br>";
  $PriceError = "";
  $priceOk = 1;
  $errormsg = "";
  $errordisplay = 0;
  //Kontrola dostępu użytkownika
  if(isset($_SESSION["user_id"]) == false) {
    header("Location: signin.php");
    exit();
  }
  else if ($_SESSION["user_ispasschanged"] == 0) {
    header("Location: settings.php");
    exit;
  }
  // Zapisywanie domyślnego lub wybranego przez użytkownika rodzaju listy pojazdów
  if(!isset($_GET["view"])) {
    $vehicleview = "all";
  }
  else if(isset($_GET["view"])) {
    $vehicleview = $_GET["view"];
  }
  // Usuwanie pojazdu
  if(isset($_GET['id'])) {
    $sql = "UPDATE vehicles SET deleted='1' WHERE id='".htmlentities($_GET['id'])."'";
    if (mysqli_query($mysqli, $sql)) {
      header("Location: vehicles.php");
    } 
    else {
      $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
      $errordisplay = 1;
    } 
  } //Dodawanie pojazdu
  else if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["addsubmit"])) {
    $brand = htmlentities($_POST['brand']);
    $model = htmlentities($_POST['model']);
    $license_plate = htmlentities($_POST['license_plate']);
    $typ = htmlentities($_POST['typ']);
    $status = htmlentities($_POST['status']);
    $year = htmlentities($_POST['year']);
    $batterylevel = htmlentities($_POST['batterylevel']);
    $batterydegradation = htmlentities($_POST['batterydegradation']);
    $sql = "INSERT INTO vehicles (`brand`, `model`, `license_plate`, `typ`, `status`, `year`, `battery_level`, `battery_degradation`) 
    VALUES ('$brand', '$model', '$license_plate', '$typ', '$status', '$year', '$batterylevel', '$batterydegradation')";

    if (mysqli_query($mysqli, $sql)) {
      $_SESSION["idcar"] = mysqli_insert_id($mysqli); //Zapisywanie ID pojazdu w sesji w celu użycia przy dodawaniu zdjęcia pojazdu
    } 
    else {
      $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
      $errordisplay = 1;
    }
  } //Edycja pojazdu
  else if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["editsubmit"])) {
    $brand = htmlentities($_POST['brand']);
    $model = htmlentities($_POST['model']);
    $license_plate = htmlentities($_POST['license_plate']);
    $typ = htmlentities($_POST['typ']);
    $status = htmlentities($_POST['status']);
    $year = htmlentities($_POST['year']);
    $batterylevel = htmlentities($_POST['batterylevel']);
    $batterydegradation = htmlentities($_POST['batterydegradation']);
    
    if(isset($_POST['maintenanceneeded']) && $_POST['maintenanceneeded'] == '1') {
    $maintenanceneeded = '1';
    }
    else {
    $maintenanceneeded = '0';
    }	 

    $sql = "UPDATE vehicles SET brand='$brand', model='$model', license_plate='$license_plate', typ='$typ', status='$status', year='$year', battery_level='$batterylevel', battery_degradation='$batterydegradation', maintenance_needed='$maintenanceneeded' WHERE id='".htmlentities($_POST['editid'])."'";

    if (mysqli_query($mysqli, $sql)) {
    } 
    else {
      $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
      $errordisplay = 1;
    }
  } 
  else if($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["flagprice"]) && $_SESSION["user_role"] != 2 ) { // Flagowanie ceny z cennika
    $sql = "UPDATE prices SET is_flagged=1 WHERE id='".htmlentities($_GET['flagprice'])."'";

    if (mysqli_query($mysqli, $sql)) {
    } 
    else {
      $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
      $errordisplay = 1;
    }  
  } 
  else if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["pricesubmit"])) { // Dodawanie ceny do cennika
    $vehicleid = htmlentities($_POST['vehicleid']);
    $newprice = htmlentities($_POST['new_price']);
    $price_start = htmlentities($_POST['price_start']);
    $price_end = htmlentities($_POST['price_end']);
    $time_unit = htmlentities($_POST['time_unit']);
    // Walidacja okresu ceny
    if ($price_start>$price_end) { 
      $errormsg = "Data końca ważności ceny nie może być przed datą rozpoczęcia ważności ceny!";
      $errordisplay = 1;
    }
    else if ($price_start==$price_end){
      $errormsg = "Data końca ważności ceny nie może być taka sama jak data rozpoczęcia ważności ceny!";
      $errordisplay = 1;
    }
    else {
      $sql = "SELECT id FROM prices WHERE vehicle_id = '$vehicleid' AND time_to > '$price_start' AND time_from <= '$price_end' AND is_flagged=0"; // Sprawdzenie czy cena z podanego okresu już istnieje
      $result = $mysqli->query($sql);
      // Dodanie ceny do cennika
      if (mysqli_num_rows($result)==0) {
        $sql = "INSERT INTO prices (`vehicle_id`, `price`, `time_unit`, `time_from`, `time_to`) VALUES ('$vehicleid', '$newprice', '$time_unit', '$price_start', '$price_end')";

        if (!mysqli_query($mysqli, $sql)) {
        $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
        $errordisplay = 1;
        }
      }
      else { //Error informujący o tym, że nowa cena już pokrywa się z cennikiem
        $PriceError = "<b>Uwaga:</b> Nowa cena nie została dodana ponieważ pokrywa się już z cennikiem.";
        $priceOk = 0;
      }
    }    
  } 
  else if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["maintenancesubmit"])) { // Serwisowanie pojazdu
    $vehicleid = htmlentities($_POST['vehicleid']);
    $servicestart = htmlentities($_POST['service_start']);
    $serviceend = htmlentities($_POST['service_end']);
    $maintenancedescription = htmlentities($_POST['maintenance_description']);
    $servicelocation = htmlentities($_POST['service_location']);
    $userid = htmlentities($_POST['user_id']);
    $creationdate = date('Y/m/d h:i:s a', time());
    // Walidacja daty serwisu
    if ($servicestart>$serviceend) { 
      $errormsg = "Data końca serwisu nie może być przed datą rozpoczęcia serwisu!";
      $errordisplay = 1;
    }
    else if ($servicestart==$serviceend){ 
      $errormsg = "Data końca serwisu nie może być taka sama jak data rozpoczęcia serwisu!";
      $errordisplay = 1;
    }
    else {
      $sql = "INSERT INTO maintenance (`vehicle_id`, `service_status`, `creation_date`, `service_start`, `service_end`, `planned`, `user_id`, `maintenance_description`, `service_location`) VALUES ('$vehicleid', '1', '$creationdate', '$servicestart', '$serviceend', '1', '$userid', '$maintenancedescription', '$servicelocation');";

      if (!mysqli_query($mysqli, $sql)) {
        $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
        $errordisplay = 1;
      }
      // Aktualizacja danych pojazdu, który będzie serwisowany
      $sql = "UPDATE vehicles SET status='W serwisie', maintenance_needed = 0 WHERE id='$vehicleid'";

      if (mysqli_query($mysqli, $sql)) {
      header("Location: service.php");
      exit();
      } 
      else {
        $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
        $errordisplay = 1;
      }
    }
  } // Wypożyczenie pojazdu
  else if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reservationsubmit"])) {
    $vehicleid = htmlentities($_POST['vehicleid']);
    $email = htmlentities($_POST['client_email']);
    $reservation_start = htmlentities($_POST['reservation_start']);
    $reservation_end = htmlentities($_POST['reservation_end']);
    $reservation_start2 = new datetime($_POST['reservation_start']);
    $reservation_end2 = new datetime($_POST['reservation_end']);
    $userid = $_SESSION["user_id"];
    $creationdate = date('Y/m/d h:i:s a', time());
    $reservation_end2days = new datetime($reservation_end2->format('Y/m/d'));
    $reservation_start2days = new datetime($reservation_start2->format('Y/m/d'));
    $fulldays = $reservation_end2days->diff($reservation_start2days);
    // Walidacja daty wypożyczenia
    if ($reservation_start>$reservation_end) {
      $errormsg = "Data końca wypożyczenia nie może być przed datą rozpoczęcia wypożyczenia!";
      $errordisplay = 1;
    }
    else if ($reservation_start==$reservation_end){
      $errormsg = "Data końca wypożyczenia nie może być taka sama jak data rozpoczęcia wypożyczenia!";
      $errordisplay = 1;
    }
    else { //Kalkulacja ceny wypożyczenia
      $sql = "SELECT id, time_unit, price FROM prices WHERE vehicle_id = '$vehicleid' AND time_from < NOW() AND time_to > NOW() AND is_flagged=0 ORDER BY id DESC LIMIT 1";
      $result = $mysqli->query($sql);
      $result_price = $result->fetch_assoc();
      $time_unit = $result_price["time_unit"];
      $price_raw = $result_price["price"];
      
      if($time_unit==="days"){
        $price = ($fulldays->days + 1) * $price_raw;
      }
      else {
        $t1 = strtotime($reservation_start);
        $t2 = strtotime($reservation_end);
        $diff = $t2 - $t1;
        $hours = ceil($diff / ( 60 * 60 ));
        $price = $hours * $price_raw;
      }

      $sql = "SELECT id FROM users WHERE email = '$email'";
      $result = $mysqli->query($sql);
      $client = $result->fetch_assoc();
      
      $sql = "INSERT INTO reservations (`vehicle_id`, `client_id`, `is_active`, `price`, `reservation_start`, `reservation_end`, `user_id`, `Creation_date`) 
      VALUES ('$vehicleid', '".$client["id"]."', '1', '$price', '$reservation_start', '$reservation_end', '$userid', '$creationdate');";

      if (!mysqli_query($mysqli, $sql)) {
        $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
        $errordisplay = 1;
      }
      // Aktualizacja danych pojazdu, który będzie wypożyczony
      $sql = "UPDATE vehicles SET status='Wypożyczony' WHERE id='$vehicleid'";

      if (mysqli_query($mysqli, $sql)) {
      header("Location: reservations.php");
      exit();
      } 
      else {
        $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
        $errordisplay = 1;
      }
    }
  }
  // Dodawanie lub edycja zdjęcia pojazdu
  if($_SERVER["REQUEST_METHOD"] === "POST" && (isset($_POST["editsubmit"]) || isset($_POST["addsubmit"])) ){
    $target_dir = "images/vehicles/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    // Sprawdzenie czy plik jest prawdziwym zdjęciem
    if($_FILES['fileToUpload']['size'] != 0) {
      $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
      if($check !== false) {
        $uploadOk = 1;
      } 
      else {
        $ImgError .= "Plik nie jest zdjęciem.<br>";
        $uploadOk = 0;
      }

      // Sprawdzenie czy zdjęcie pojazdu już istnieje
      if (file_exists($target_file)) {
        $ImgError .= "Takie zdjęcie już istnieje.<br>";
        $uploadOk = 0;
      }

      // Sprawdzenie rozmiaru pliku
      if ($_FILES["fileToUpload"]["size"] > 5000000) {
        $ImgError .= "Zdjęcie jest zbyt duże.<br>";
        $uploadOk = 0;
      }

      // Zezwolenie na wybrane formaty plików
      if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        $ImgError .= "Tylko pliki w formacie JPG, JPEG i PNG są możliwe do przesłania.<br>";
        $uploadOk = 0;
      }

      // Sprawdzenie czy $uploadOk zostało ustawione na 0 przez błąd
      if ($uploadOk == 0) {
        $ImgError .= "Twoje zdjęcie nie zostało wrzucone.<br>";
        // Próba uploadu zdjęcia jeżeli do tej pory nie pojawiły się błędy
      } 
      else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
          if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["editsubmit"])) {
            $sql = "UPDATE vehicles SET image='$target_file' WHERE id='".htmlentities($_POST['editid'])."'";

            if (mysqli_query($mysqli, $sql)) {
            } 
            else {
              $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
              $errordisplay = 1;
            }
          }
          else if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["addsubmit"])) {
            $sql = "UPDATE vehicles SET image='$target_file' WHERE id='".$_SESSION["idcar"]."'";
            if (mysqli_query($mysqli, $sql)) {
            } 
            else {
              $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
              $errordisplay = 1;
            }
          }
        } 
        else {
          $ImgError .= "Wystąpiły następujące błędy podczas wrzucania pliku.";
        }
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="pl-PL">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Elecar - Pojazdy</title>
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
    <div class="row text-wrap">
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
                <a class="nav-link d-flex align-items-center gap-2 fs-6 active" aria-current="page" href="#">
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
          <h2>Pojazdy</h2>
          <div class="d-flex flex-wrap">
            <?php if($_SESSION["user_role"] != 3) { ?> <button type="button" class="text-nowrap btn btn-lg btn-outline-secondary me-2 mb-1" data-toggle="modal" data-target="#ModalCenteraddvehicle">Dodaj pojazd</button> <?php } ?>
            <form method="get">
              <select class="btn btn-lg btn-outline-secondary mb-1" name="view" onchange="this.form.submit();">  
                <option value="all" <?php echo (($vehicleview == "all")?"selected":"" );?>>Wszystkie Pojazdy</option>  
                <option value="Hulajnoga" <?php echo (($vehicleview == "Hulajnoga")?"selected":"" );?>>Tylko Hulajnogi</option>  
                <option value="Auto" <?php echo (($vehicleview == "Auto")?"selected":"" );?>>Tylko Auta</option>  
              </select>
            </form>
          </div>
          <?php if($_SESSION["user_role"] != 3) { ?>  
          <div class="modal fade" id="ModalCenteraddvehicle" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLongTitle"><img src="images/logo.png" alt="" width="40" height="40">  Dodaj pojazd</h5>
                </div>
                <form method="post" enctype="multipart/form-data">
                  <div class="modal-body">
                    <div class="form-group">
                      <label for="formGroupExampleInput">Zdjęcie</label>
                      <input type="file" class="form-control" id="formGroupExampleInput" name="fileToUpload" />
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Marka</label>
                      <input type="text" class="form-control" id="formGroupExampleInput" name="brand"  required />
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Model</label>
                      <input type="text" class="form-control" id="formGroupExampleInput" name="model"  required >
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Numer rejestracyjny (Tylko dla aut)</label>
                      <input type="text" minlength="7" maxlength="8" class="form-control" style="text-transform:uppercase" id="formGroupExampleInput" name="license_plate" >
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Typ</label>
                      <select class="form-control" id="formGroupExampleInput" name="typ">
                        <option value="Auto">Auto</option>
                        <option value="Hulajnoga">Hulajnoga</option> 
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Status</label>
                      <select class="form-control" id="formGroupExampleInput" name="status">
                        <option value="Dostępny">Dostępny</option>
                        <option value="Wypożyczony">Wypożyczony</option>
                        <option value="W serwisie">W serwisie</option> 
                        <option value="Rozładowany">Rozładowany</option>  
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Rocznik</label>
                      <input type="text" maxlength="4" pattern="[0-9]+" class="form-control" id="formGroupExampleInput" name="year"  required >
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Poziom baterii</label>
                      <input type="number" min="0" max="100" class="form-control" id="formGroupExampleInput" name="batterylevel"  required >
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Stan baterii</label>
                      <input type="number" min="0" max="100" class="form-control" id="formGroupExampleInput" name="batterydegradation"  required >
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Zamknij</button>
                    <button type="submit" value="Edit Vehicle" name="addsubmit" class="btn btn-primary">Dodaj</button>
                  </div>
                </form>
              </div>
            </div>
          </div>      
          <?php } ?>
        </div>
        <?php if($errordisplay == 1) { ?> 
        <div class="alert alert-danger alert-dismissible fade show mt-1" role="alert">
          <?php echo $errormsg; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php }
        if($uploadOk == 0) { ?> 
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php echo $ImgError; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php } 
        if($priceOk == 0) { ?> 
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php echo $PriceError; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php } ?>
        <table class="table table-hover">
          <thead class="table-dark">
            <tr>
              <th scope="col">Zdjęcie</th>
              <th scope="col">Informacje szczegółowe</th>
              <th scope="col">Status</th>
              <th scope="col">Bateria</th>
              <th scope="col" class="buttonlist">Akcja</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            if ($vehicleview == "Hulajnoga") {
              $sql = sprintf("SELECT id, license_plate, brand, model, typ, status, year, image, maintenance_needed, maintenance_last, battery_level, battery_degradation FROM vehicles WHERE deleted='0' AND typ='Hulajnoga'");
            }
            else if ($vehicleview == "Auto") {
              $sql = sprintf("SELECT id, license_plate, brand, model, typ, status, year, image, maintenance_needed, maintenance_last, battery_level, battery_degradation FROM vehicles WHERE deleted='0' AND typ='Auto'"); 
            }
            else{
              $sql = sprintf("SELECT id, license_plate, brand, model, typ, status, year, image, maintenance_needed, maintenance_last, battery_level, battery_degradation FROM vehicles WHERE deleted='0'");
            }
            $result = $mysqli->query($sql);
            while($row = $result->fetch_assoc()) {           
            ?>
            <tr>
              <td class="text-center"><img class="img-fluid" src="<?php echo $row['image']; ?>" alt="" width="200px"></td>
              <td><?php echo "<b>Marka: </b>".$row['brand']."</br><b>Model: </b>".$row['model'].(($row['typ']=='Auto')?"</br><b>Numer rejestracyjny: </b>".$row['license_plate']:"")."</br><b>Typ: </b>".$row['typ']."</br><b>Rocznik: </b>".$row['year'];                              
                $sqlprices = sprintf("SELECT * FROM prices WHERE time_from < NOW() AND time_to > NOW() AND vehicle_id = '".$row["id"]."' AND is_flagged=0 ORDER BY id DESC LIMIT 1");
                $resultprices = $mysqli->query($sqlprices);
                
                if (mysqli_num_rows($resultprices)==0) {
                echo "</br><p style='color:red;'><b>Cena:</b> Brak ceny! Proszę zaktualizować!</p>";
                }
                else {
                  while($pricerow = $resultprices->fetch_assoc()) {
                    echo "</br><b>Cena: </b>".$pricerow['price']." PLN na ".(($pricerow['time_unit']=='days')?"dzień":"godzinę");
                  }
                }        
                ?>
              </td>
              <td><?php if ($row['status']=="Dostępny" || $row['status']=="Rozładowany") {
                  echo "<b>Status: </b>".$row['status']."<br><b>Czy potrzebny serwis? </b>".(($row['maintenance_needed']=='1')?"Tak":"Nie").(($row['maintenance_last']!='0000-00-00')?"</br><b>Ostatni serwis: </b>".$row['maintenance_last']:"");
                }
                else if ($row['status']=="Wypożyczony") {  
                  $sql_reservations = "SELECT * FROM reservations WHERE vehicle_id = '".$row["id"]."' AND is_active = 1";
                  $result_reservations = $mysqli->query($sql_reservations);
                  $reservation = $result_reservations->fetch_assoc();
                  echo "<b>Status: </b>".$row['status']."<br><b>Początek wypożyczenia: </b><br>".$reservation["reservation_start"]."<br><b>Koniec wypożyczenia: </b><br>".$reservation["reservation_end"]."<br><b>Ostatni serwis: </b>".$row["maintenance_last"];
                }
                else if ($row['status']=="W serwisie") {
                  $sql_maintenance = "SELECT * FROM maintenance WHERE vehicle_id = '".$row["id"]."' AND service_status = 1";
                  $result_maintenance = $mysqli->query($sql_maintenance);
                  $maintenance = $result_maintenance->fetch_assoc();
                  $sql_maintenanceuser = "SELECT firstname, lastname FROM users WHERE id = '".$maintenance["user_id"]."'";
                  $result_maintenanceuser = $mysqli->query($sql_maintenanceuser);
                  $maintenanceuser = $result_maintenanceuser->fetch_assoc();
                  echo "<b>Status: </b>".$row['status']."<br><b>Początek serwisu: </b><br>".$maintenance["service_start"]."<br><b>Koniec serwisu: </b><br>".$maintenance["service_end"]."<br><b>Przypisany pracownik: </b>".$maintenanceuser["firstname"]." ".$maintenanceuser["lastname"];
                }
                ?>
              </td>
              <td><?php echo "<b>Poziom baterii: </b>".$row['battery_level']."%"."</br><b>Stan baterii: </b>".$row['battery_degradation']."%"; ?></td>
              <td align="center" class="buttonlist"><button type='button' title="Wypożycz" <?php if($row['status'] == "Dostępny" && $_SESSION["user_role"] != 2 && mysqli_num_rows($resultprices)!=0) { ?> class='btn btn-success' <?php } else { ?>class='btn btn-secondary' <?php } ?> data-toggle='modal' <?php if($row['status'] == "Dostępny" && $_SESSION["user_role"] != 2 && mysqli_num_rows($resultprices)!=0) { echo "data-target='#ModalCenterReservation".$row["id"]."'";} ?>><i class="bi bi-cart3"></i></button>
                <button type='button' title="Serwisuj" <?php if($row['status'] == "Dostępny" && $_SESSION["user_role"] != 3) { ?> class='btn btn-info' <?php } else { ?>class='btn btn-secondary' <?php } ?> data-toggle='modal' <?php if($row['status'] == "Dostępny" && $_SESSION["user_role"] != 3) { echo "data-target='#ModalCenterMaintenance".$row["id"]."'";} ?>><i class="bi bi-tools"></i></button> 
                <button type='button' title="Cennik" <?php if($_SESSION["user_role"] != 2) { ?> class='btn btn-warning' <?php } else { ?>class='btn btn-secondary' <?php } ?> data-toggle='modal' <?php if($_SESSION["user_role"] != 2) { echo "data-target='#ModalCenterPrices".$row["id"]."'";} ?>><i class="bi bi-tag"></i></button>
                <button type='button' title="Edytuj" class='btn btn-warning' data-toggle='modal' <?php echo "data-target='#editModalCenter".$row["id"]."'"; ?>><i class="bi bi-pencil-square"></i></button>
                <a <?php echo 'href="vehicles.php?id='.$row["id"].'"'; ?> class='btn btn-danger' title="Usuń"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
            <tr class="flex-row d-md-none">
              <td colspan="4" align="center"><button type='button' title="Wypożycz" <?php if($row['status'] == "Dostępny" && $_SESSION["user_role"] != 2 && mysqli_num_rows($resultprices)!=0) { ?> class='btn btn-success' <?php } else { ?>class='btn btn-secondary' <?php } ?> data-toggle='modal' <?php if($row['status'] == "Dostępny" && $_SESSION["user_role"] != 2 && mysqli_num_rows($resultprices)!=0) { echo "data-target='#ModalCenterReservation".$row["id"]."'";} ?>><i class="bi bi-cart3"></i></button>
                <button type='button' title="Serwisuj" <?php if($row['status'] == "Dostępny" && $_SESSION["user_role"] != 3) { ?> class='btn btn-info' <?php } else { ?>class='btn btn-secondary' <?php } ?> data-toggle='modal' <?php if($row['status'] == "Dostępny" && $_SESSION["user_role"] != 3) { echo "data-target='#ModalCenterMaintenance".$row["id"]."'";} ?>><i class="bi bi-tools"></i></button> 
                <button type='button' title="Cennik" <?php if($_SESSION["user_role"] != 2) { ?> class='btn btn-warning' <?php } else { ?>class='btn btn-secondary' <?php } ?> data-toggle='modal' <?php if($_SESSION["user_role"] != 2) { echo "data-target='#ModalCenterPrices".$row["id"]."'";} ?>><i class="bi bi-tag"></i></button>
                <button type='button' title="Edytuj" class='btn btn-warning' data-toggle='modal' <?php echo "data-target='#editModalCenter".$row["id"]."'"; ?>><i class="bi bi-pencil-square"></i></button>
                <a <?php echo 'href="vehicles.php?id='.$row["id"].'"'; ?> class='btn btn-danger' title="Usuń"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
            <div class="modal fade" <?php echo 'id="editModalCenter'.$row["id"].'"'; ?> tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"><img src="images/logo.png" alt="" width="40" height="40">  Edytuj pojazd</h5>
                  </div>  
                  <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                      <div class="form-group">
                        <input type="hidden" class="form-control" id="formGroupExampleInput" name="editid"  <?php echo 'value="'.$row["id"].'"'; ?> readonly/>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Zdjęcie</label>
                        <input type="file" class="form-control" id="formGroupExampleInput" name="fileToUpload" />
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Marka</label>
                        <input type="text" class="form-control" id="formGroupExampleInput" name="brand" <?php echo 'value="'.$row["brand"].'"'; ?> required />
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Model</label>
                        <input type="text" class="form-control" id="formGroupExampleInput" name="model" <?php echo 'value="'.$row["model"].'"'; ?> required >
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Numer rejestracyjny (Tylko dla aut)</label>
                        <input type="text" minlength="7" maxlength="8" style="text-transform:uppercase" class="form-control" id="formGroupExampleInput" name="license_plate"  <?php echo 'value="'.$row["license_plate"].'"'; ?> >
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Typ</label>
                        <select class="form-control" id="formGroupExampleInput" name="typ">
                          <option value="Auto" <?php echo (($row['typ']=='Auto')?'selected="selected"':""); ?>>Auto</option>
                          <option value="Hulajnoga" <?php echo (($row['typ']=='Hulajnoga')?'selected="selected"':""); ?>>Hulajnoga</option> 
                        </select>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Status</label>
                        <select class="form-control" id="formGroupExampleInput" name="status">
                          <option value="Dostępny" <?php echo (($row['status']=='Dostępny')?'selected="selected"':""); ?>>Dostępny</option>
                          <option value="Wypożyczony" <?php echo (($row['status']=='Wypożyczony')?'selected="selected"':""); ?>>Wypożyczony</option>
                          <option value="W serwisie" <?php echo (($row['status']=='W serwisie')?'selected="selected"':""); ?>>W serwisie</option> 
                          <option value="Rozładowany" <?php echo (($row['status']=='Rozładowany')?'selected="selected"':""); ?>>Rozładowany</option>  
                        </select>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Rocznik</label>
                        <input type="text" maxlength="4" pattern="[0-9]+" class="form-control" id="formGroupExampleInput" name="year" <?php echo 'value="'.$row["year"].'"'; ?> required >
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Poziom baterii</label>
                        <input type="number" min="0" max="100" class="form-control" id="formGroupExampleInput" name="batterylevel" <?php echo 'value="'.$row["battery_level"].'"'; ?> required >
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Stan baterii</label>
                        <input type="number" min="0" max="100" class="form-control" id="formGroupExampleInput" name="batterydegradation" <?php echo 'value="'.$row["battery_degradation"].'"'; ?> required >
                      </div>
                      <div class="form-group mt-1"> 
                        <input type="checkbox" id="formGroupExampleInput" name="maintenanceneeded" value="1"  <?php echo (($row['maintenance_needed']=='1')?"checked":""); ?> />
                        <label for="formGroupExampleInput">Czy potrzebny serwis?</label>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Zamknij</button>
                      <button type="submit" value="Edit Vehicle" name="editsubmit" class="btn btn-primary">Edytuj</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!-- New Modal -->
            <div class="modal fade" <?php echo 'id="ModalCenterPrices'.$row["id"].'"'; ?> tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"><img src="images/logo.png" alt="" width="40" height="40">  Cennik Pojazdu</h5>
                  </div>
                  <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                      <div class="form-group">
                        <input type="hidden" class="form-control" id="formGroupExampleInput" name="vehicleid"  <?php echo 'value="'.$row["id"].'"'; ?> readonly/>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Nowa cena</label>
                        <input type="number" step=".01" min="0" class="form-control" id="formGroupExampleInput" name="new_price" required >
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Cena za</label>
                        <select class="form-control" id="formGroupExampleInput" name="time_unit" required>
                          <option value="days">Dzień</option>
                          <option value="hours">Godzinę</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Początek ważności ceny</label>
                        <input type="datetime-local" class="form-control" id="formGroupExampleInput" name="price_start" required/>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Koniec ważności ceny</label>
                        <input type="datetime-local" class="form-control" id="formGroupExampleInput" name="price_end" required >
                      </div>
                      <br><b>Historia Cen:</b><br>
                      <?php                            
                      $sql_prices = "SELECT * FROM prices WHERE vehicle_id = '".$row["id"]."' ORDER BY time_from DESC";
                      $result_prices = $mysqli->query($sql_prices);
                      while($price_row = $result_prices->fetch_assoc()) {
                        ?>
                        <hr>
                        <div class="d-flex">
                          <div class="p-2 flex-grow-1"><?php echo (($price_row['is_flagged']=='1')?"<p style='color:red;'><b>Oflagowana Cena</b></p>":"").(($price_row['time_from']<date("Y-m-d H:i:s") && $price_row['time_to']>date("Y-m-d H:i:s") && $price_row['is_flagged']=='0')?"<p style='color:green;'><b>Aktualna Cena</b></p>":"")."<b>Cena:</b> ".$price_row["price"]." PLN na ".(($price_row['time_unit']=='days')?"dzień":"godzinę")."<br> <b>Od:</b> ".$price_row["time_from"]." <b>Do:</b> ".$price_row["time_to"]."<br>"; ?></div>
                          <div class="p-2"><a <?php if($price_row['is_flagged']=='0') { echo 'href="vehicles.php?flagprice='.$price_row["id"].'"'; ?> class='btn btn-danger btn-sm' title="Oflaguj cenę" <?php } else {?>class='btn btn-secondary btn-sm'<?php } ?>><i class="bi bi-flag-fill h4"></i></a></div>
                        </div>
                      <?php } ?>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Zamknij</button>
                      <button type="submit" value="Price" name="pricesubmit" class="btn btn-primary">Zmień cenę</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!-- New Modal -->
            <div class="modal fade" <?php echo 'id="ModalCenterMaintenance'.$row["id"].'"'; ?> tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"><img src="images/logo.png" alt="" width="40" height="40">  Serwisuj Pojazd</h5>
                  </div>
                  <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                      <div class="form-group">
                        <input type="hidden" class="form-control" id="formGroupExampleInput" name="vehicleid"  <?php echo 'value="'.$row["id"].'"'; ?> readonly/>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Początek serwisu</label>
                        <input type="datetime-local" class="form-control" id="formGroupExampleInput" name="service_start" required />
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Koniec serwisu</label>
                        <input type="datetime-local" class="form-control" id="formGroupExampleInput" name="service_end" required >
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Opis serwisu</label>
                        <textarea class="form-control" rows="3" id="formGroupExampleInput" name="maintenance_description" required ></textarea>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Miejsce serwisowania</label>
                        <input type="text" class="form-control" id="formGroupExampleInput" name="service_location" required >
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Przypisany pracownik</label>
                        <select class="form-control" id="formGroupExampleInput" name="user_id">
                          <?php 
                            $sqlmaintenanceusers = "SELECT * FROM users where role_id=2";
                            $resultmaintenanceusers = $mysqli->query($sqlmaintenanceusers);
                            while($maintenanceuserrow = $resultmaintenanceusers->fetch_assoc()) {
                          ?>
                          <option <?php echo 'value="'.$maintenanceuserrow['id'].'"'; ?>>
                            <?php echo $maintenanceuserrow['firstname']." ".$maintenanceuserrow['lastname'];?>
                          </option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Zamknij</button>
                      <button type="submit" value="Maintenance" name="maintenancesubmit" class="btn btn-primary">Serwisuj</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!-- New Modal -->
            <div class="modal fade" <?php echo 'id="ModalCenterReservation'.$row["id"].'"'; ?> tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"><img src="images/logo.png" alt="" width="40" height="40">  Wypożycz Pojazd</h5>
                  </div>
                  <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                      <?php
                        echo "<b>Pojazd: </b>".$row['brand']." ".$row['model']."<br>";                            
                        $sql_prices = sprintf("SELECT * FROM prices WHERE vehicle_id = '".$row["id"]."' AND time_from < NOW() AND time_to > NOW() AND is_flagged=0 ORDER BY id DESC LIMIT 1");
                        $result_prices = $mysqli->query($sql_prices);
                        while($price_row = $result_prices->fetch_assoc()) {
                          echo "<b>Cena:</b> ".$price_row["price"]." PLN na ".(($price_row['time_unit']=='days')?"dzień":"godzinę")."<br><br>";
                        }
                      ?>
                      <div class="form-group">
                        <input type="hidden" class="form-control" id="formGroupExampleInput" name="vehicleid"  <?php echo 'value="'.$row["id"].'"'; ?> readonly/>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Klient</label>
                        <select class="form-control" id="formGroupExampleInput" name="client_email" required>  
                          <?php
                          $sql_clients = sprintf("SELECT id, firstname, lastname, email FROM users WHERE isdeleted='0' AND role_id = 4 ORDER BY lastname");
                          $result_clients = $mysqli->query($sql_clients);
                          while($clients_row = $result_clients->fetch_assoc()) {  ?>
                            <option  <?php 
                              if (isset($_GET["user_client"])){
                                echo 'value="'.$clients_row["email"].'"'.(($_GET["user_client"]  == $clients_row["id"])?"selected":"" );?>><?php echo $clients_row["lastname"]." ".$clients_row["firstname"]." - ".$clients_row["email"];?>
                            </option> 
                            <?php }
                            else {
                            echo 'value="'.$clients_row["email"].'"';?>><?php echo $clients_row["lastname"]." ".$clients_row["firstname"]." - ".$clients_row["email"];?></option> 
                          <?php }} ?> 
                        </select>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Rozpoczęcie wypożyczenia</label>
                        <input type="datetime-local" class="form-control" id="formGroupExampleInput" name="reservation_start" required />
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Zakończenie wypożyczenia</label>
                        <input type="datetime-local" class="form-control" id="formGroupExampleInput" name="reservation_end" required >
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Zamknij</button>
                      <button type="submit" value="Reservation" name="reservationsubmit" class="btn btn-primary">Wypożycz</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <?php } ?>
          </tbody>
        </table>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.2/dist/chart.umd.js" integrity="sha384-eI7PSr3L1XLISH8JdDII5YN/njoSsxfbrkCTnJrzXt+ENP5MOVBxD+l6sEG4zoLp" crossorigin="anonymous"></script><script src="dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
  </script>
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
    integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"
    integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous">
  </script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js"
    integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous">
  </script>
</body>
</html>