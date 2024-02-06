<?php  
  session_start();
  
  $errordisplay = 0;
  $errormsg = "";
  //Połączenie z bazą danych
  $mysqli = require __DIR__ . "/database.php";
  //Kontrola dostępu użytkownika
  if(isset($_SESSION["user_id"]) == false) {
    header("Location: signin.php");
    exit();
  }
  else if ($_SESSION["user_ispasschanged"] == 0)
  {
    header("Location: settings.php");
    exit;
  }
  // Zapisywanie domyślnego lub wybranego przez użytkownika rodzaju listy serwisów
  if(!isset($_GET["view"])) {
    $maintenanceview = "active";
  }
  else if(isset($_GET["view"])) {
    $maintenanceview  = $_GET["view"];
  }
  // Zakończenie serwisu
  if(isset($_GET['id']) && $_SESSION["user_role"] != 3) {
    $sql = "SELECT vehicle_id FROM maintenance WHERE id = '".htmlentities($_GET['id'])."'";
    $result = $mysqli->query($sql);
    $vehicle = $result->fetch_assoc();
    $vehicle_id = $vehicle["vehicle_id"]; 
    $last_maintenance_date = date('Y-m-d');
    $sql = "UPDATE vehicles SET status='Dostępny', maintenance_last='$last_maintenance_date' WHERE id='$vehicle_id'";
    if (mysqli_query($mysqli, $sql)) {
    } 
    else {
        $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
        $errordisplay = 1;
    }
    $sql = "UPDATE maintenance SET service_status='0', service_end='$last_maintenance_date' WHERE id='".htmlentities($_GET['id'])."'";
    if (mysqli_query($mysqli, $sql)) {
      header("Location: service.php");
    } 
    else {
      $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
      $errordisplay = 1;
    }
  } 
  else if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["editsubmit"])) { //Edycja serwisu
    $service_start = htmlentities($_POST['service_start']);
    $service_end = htmlentities($_POST['service_end']);
    $maintenance_description = htmlentities($_POST['maintenance_description']);
    $servicelocation = htmlentities($_POST['service_location']);
    $userid = htmlentities($_POST['user_id']);
    //Walidacja daty serwisowania
    if ($service_start>$service_end) { 
      $errormsg = "Data końca serwisu nie może być przed datą rozpoczęcia serwisu!";
      $errordisplay = 1;
    }
    else if ($service_start==$service_end){
      $errormsg = "Data końca serwisu nie może być taka sama jak data rozpoczęcia serwisu!";
      $errordisplay = 1;
    }
    else { //Aktualizacja danych serwisu
      $sql = "UPDATE maintenance SET service_start='$service_start', service_end='$service_end', maintenance_description='$maintenance_description', service_location='$servicelocation', user_id='$userid'  WHERE id='".htmlentities($_POST['editid'])."'";
      if (mysqli_query($mysqli, $sql)) {
      } 
      else {
        $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
        $errordisplay = 1;
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="pl-PL">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Elecar - Serwis</title>
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
                <a class="nav-link d-flex align-items-center gap-2 fs-6 active" aria-current="page" href="#">
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
          <h1 class="h2">Serwisowane pojazdy</h1>
          <div class="d-flex">
            <form method="get">
              <select class="btn btn-lg btn-outline-secondary" name="view" onchange="this.form.submit();">  
                <option value="all" <?php echo (($maintenanceview  == "all")?"selected":"" );?>>Wszystkie serwisy</option>  
                <option value="active" <?php echo (($maintenanceview  == "active")?"selected":"" );?>>Aktualne serwisy</option>  
                <option value="inactive" <?php echo (($maintenanceview  == "inactive")?"selected":"" );?>>Nieaktualne serwisy</option>  
              </select>
            </form>   
          </div>               
        </div>
        <?php if($errordisplay == 1) { ?> 
        <div class="alert alert-danger alert-dismissible fade show mt-1" role="alert">
          <?php echo $errormsg; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php } ?>
        <table class="table table-hover">
          <thead class="table-dark">
            <tr>
              <th scope="col">Zdjęcie</th>
              <th scope="col">Informacje szczegółowe</th>
              <th scope="col">Data serwisowania</th>
              <th scope="col">Szczegóły serwisu</th>
              <?php if($_SESSION["user_role"] != 3) { ?> <th scope="col" class="buttonlist">Akcja</th> <?php } ?>
            </tr>
          </thead>
          <tbody>
            <?php 
            if ($maintenanceview  == "active") {
              $sql = sprintf("SELECT maintenance.id, vehicle_id, service_status, creation_date, service_start, service_end, user_id, maintenance_description, service_location, vehicles.image, vehicles.brand, vehicles.model, vehicles.typ, vehicles.status, vehicles.year, vehicles.license_plate, users.firstname, users.lastname FROM maintenance INNER JOIN vehicles ON maintenance.vehicle_id = vehicles.id INNER JOIN users ON maintenance.user_id = users.id WHERE service_status = 1 ORDER BY creation_date desc");
            }
            else if ($maintenanceview  == "inactive") {
            $sql = sprintf("SELECT maintenance.id, vehicle_id, service_status, creation_date, service_start, service_end, user_id, maintenance_description, service_location, vehicles.image, vehicles.brand, vehicles.model, vehicles.typ, vehicles.status, vehicles.year, vehicles.license_plate, users.firstname, users.lastname FROM maintenance INNER JOIN vehicles ON maintenance.vehicle_id = vehicles.id INNER JOIN users ON maintenance.user_id = users.id WHERE service_status = 0 ORDER BY creation_date desc");
            }
            else {
            $sql = sprintf("SELECT maintenance.id, vehicle_id, service_status, creation_date, service_start, service_end, user_id, maintenance_description, service_location, vehicles.image, vehicles.brand, vehicles.model, vehicles.typ, vehicles.status, vehicles.year, vehicles.license_plate, users.firstname, users.lastname FROM maintenance INNER JOIN vehicles ON maintenance.vehicle_id = vehicles.id INNER JOIN users ON maintenance.user_id = users.id ORDER BY creation_date desc");
            }
            $result = $mysqli->query($sql);
            while($row = $result->fetch_assoc()) {
            ?>
            <tr>
              <td class="text-center"><img class="img-fluid" src="<?php echo $row['image']; ?>" alt="" width="200px"></td>
              <td><?php echo "<b>Marka: </b>".$row['brand']."</br><b>Model: </b>".$row['model'].(($row['typ']=='Auto')?"</br><b>Numer Rejestracyjny: </b>".$row['license_plate']:"")."</br><b>Typ: </b>".$row['typ']."</br><b>Status: </b>".$row['status']."</br><b>Rocznik: </b>".$row['year']; ?></td>
              <td><?php echo "<b>Początek serwisu: </b>".$row['service_start']."</br><b>Koniec serwisu: </b>".$row['service_end']; ?></td>
              <td><?php echo "<b>Opis usługi: </b>".$row['maintenance_description']."</br><b>Miejsce serwisowania: </b>".$row['service_location']."</br><b>Przypisany pracownik: </b>".$row['firstname']." ".$row['lastname']; ?></td>
              <?php if($_SESSION["user_role"] != 3) { ?><td align="center" class="buttonlist"><button type='button' title="Edytuj" class='btn btn-warning' data-toggle='modal' <?php echo "data-target='#editModalCenter".$row["id"]."'"; ?>><i class="bi bi-pencil-square"></i></button>
              <a <?php if($row["service_status"] == 1 && $row["service_start"]<date("Y-m-d H:i:s")) { echo 'href="service.php?id='.$row["id"].'"'; ?> class='btn btn-danger' title="Zakończ Serwis" <?php } else { ?>class='btn btn-secondary' <?php } ?> ><i class="bi bi-tools"></i></a></td><?php } ?>
            </tr>
            <tr class="flex-row d-md-none">
              <?php if($_SESSION["user_role"] != 3) { ?><td align="center" colspan="4"><button type='button' title="Edytuj" class='btn btn-warning' data-toggle='modal' <?php echo "data-target='#editModalCenter".$row["id"]."'"; ?>><i class="bi bi-pencil-square"></i></button>
              <a <?php if($row["service_status"] == 1 && $row["service_start"]<date("Y-m-d H:i:s")) { echo 'href="service.php?id='.$row["id"].'"'; ?> class='btn btn-danger' title="Zakończ Serwis" <?php } else { ?>class='btn btn-secondary' <?php } ?> ><i class="bi bi-tools"></i></a></td><?php } ?>
            </tr>
            <div class="modal fade" <?php echo 'id="editModalCenter'.$row["id"].'"'; ?> tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"><img src="images/logo.png" alt="" width="40" height="40">  Edytuj Serwis</h5>
                  </div>
                  <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                      <div class="form-group">
                        <input type="hidden" class="form-control" id="formGroupExampleInput" name="editid"  <?php echo 'value="'.$row["id"].'"'; ?> readonly/>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Początek serwisu</label>
                        <input type="datetime-local" class="form-control" id="formGroupExampleInput" name="service_start" <?php echo 'value="'.$row["service_start"].'"'; ?> required >
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Koniec serwisu</label>
                        <input type="datetime-local" class="form-control" id="formGroupExampleInput" name="service_end" <?php echo 'value="'.$row["service_end"].'"'; ?> required >
                      </div> 
                      <div class="form-group">
                        <label for="formGroupExampleInput">Opis serwisu</label>
                        <textarea class="form-control" rows="3" id="formGroupExampleInput" name="maintenance_description" required ><?php echo $row["maintenance_description"]; ?></textarea>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Miejsce serwisowania</label>
                        <input type="text" maxlength="50" class="form-control" id="formGroupExampleInput" name="service_location" <?php echo 'value="'.$row["service_location"].'"'; ?> required>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Przypisany pracownik</label>
                        <select class="form-control" id="formGroupExampleInput" name="user_id" required>
                          <?php 
                            $sqlmaintenanceusers = "SELECT * FROM users where role_id=2";
                            $resultmaintenanceusers = $mysqli->query($sqlmaintenanceusers);
                            while($maintenanceuserrow = $resultmaintenanceusers->fetch_assoc()) {
                          ?>
                          <option <?php echo 'value="'.$maintenanceuserrow['id'].'"'.(($row['user_id']==$maintenanceuserrow['id'])?'selected="selected"':""); ?>>
                            <?php echo $maintenanceuserrow['firstname']." ".$maintenanceuserrow['lastname'];?>
                          </option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Zamknij</button>
                      <button type="submit" value="Edit Service" name="editsubmit" class="btn btn-primary">Edytuj</button>
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