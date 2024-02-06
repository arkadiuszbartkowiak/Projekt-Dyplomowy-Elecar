<?php  
  session_start();
  $errordisplay = 0;
  //Połączenie z bazą danych
  $mysqli = require __DIR__ . "/database.php";
  //Kontrola dostępu użytkownika
  if(isset($_SESSION["user_id"]) == false) {
    header("Location: signin.php");
    exit();
  } 
  else if ($_SESSION["user_ispasschanged"] == 0) {
    header("Location: settings.php");
    exit;
  }
  else if($_SESSION["user_role"] == 2) {
    header("Location: index.php");
    exit;
  }
  // Zapisywanie domyślnego lub wybranego przez użytkownika rodzaju listy użytkowników/klientów
  if (isset($_GET["user_client"])) {
    $userview = "klient";
  }
  else if((!isset($_GET["view"]) && $_SESSION["user_role"] == 1)) {
    $userview = "all";
  }
  else if((!isset($_GET["view"]) || isset($_GET["view"])) && $_SESSION["user_role"] != 1) {
    $userview = "klient";
  }
  else if(isset($_GET["view"]) && $_SESSION["user_role"] == 1) {
    $userview = $_GET["view"];
  }
  // 'Zapomnienie' klienta
  if(isset($_GET['id'])) {
    $sql = "SELECT * FROM users WHERE id = '".htmlentities($_GET['id'])."'";
    $result = $mysqli->query($sql);
    $user = $result->fetch_assoc();
    // Hashowanie danych klienta
    $sql = "UPDATE users SET isdeleted='1', firstname=SHA2('".$user["firstname"]."', 256), lastname=SHA2('".$user["lastname"]."', 256), email=SHA2('".$user["email"]."', 256), birth=SHA2('".$user["birth"]."', 256), pesel=SHA2('".$user["pesel"]."', 256), phone=SHA2('".$user["phone"]."', 256), address_city=SHA2('".$user["address_city"]."', 256), address_zipcode=SHA2('".$user["address_zipcode"]."', 256), address_street=SHA2('".$user["address_street"]."', 256), address_house=SHA2('".$user["address_house"]."', 256), address_apartment=SHA2('".$user["address_apartment"]."', 256)  WHERE id = '".htmlentities($_GET['id'])."'";
    if (mysqli_query($mysqli, $sql)) {
      header("Location: clients.php");
    } 
    else {
      $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
      $errordisplay = 1;
    }
  } //Tworzenie nowego użytkownika lub klienta
  else if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["addusersubmit"])) {
    $firstname = htmlentities($_POST['firstname']);
    $lastname = htmlentities($_POST['lastname']);
    $email = htmlentities($_POST['email']);
    $roleid = htmlentities($_POST['roleid']);
    $birth = htmlentities($_POST['birth']);
    $pesel = htmlentities($_POST['pesel']);
    $phone = htmlentities($_POST['phone']);
    $address_city = htmlentities($_POST['address_city']);
    $address_zipcode = htmlentities($_POST['address_zipcode']);
    $address_street = htmlentities($_POST['address_street']);
    $address_house = htmlentities($_POST['address_house']);
    $address_apartment = htmlentities($_POST['address_apartment']);
    $sql = "SELECT * FROM users WHERE email = '$email' OR pesel = '$pesel'";
    $result = $mysqli->query($sql);
    if (mysqli_num_rows($result)>0) { //Zabezpieczenie przed stworzeniem użytkownika/klienta z tym samym peselem lub adresem e-mail
      $errormsg = "Tworzenie anulowane! Osoba o danym peselu / e-mailu już istnieje w systemie!";
      $errordisplay = 1;
    } 
    else if ($_POST['password'] != $_POST['repeatpassword']){
      $errormsg = "Hasła nie zgadzają się!";
      $errordisplay = 1;
    } //Tworzenie użytkownika
    else if (isset($_POST["password"])) {
      if ($_POST['password'] == $_POST['repeatpassword']){
        $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $sqlcreateuser = "INSERT INTO users (`email`, `firstname`, `lastname`, `birth`, `pesel`, `phone`, `address_city`, `address_zipcode`, `address_street`, `address_house`, `address_apartment`, `role_id`, `password_hash`) 
                VALUES ('$email', '$firstname', '$lastname', '$birth', '$pesel', '$phone', '$address_city', '$address_zipcode', '$address_street', '$address_house', '$address_apartment', '$roleid', '$password_hash');";
      }

      if (mysqli_query($mysqli, $sqlcreateuser)) {
      } 
      else {
        $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
        $errordisplay = 1;
      }
    } //Tworzenie klienta
    else {
      $sqlcreateuser = "INSERT INTO users (`email`, `firstname`, `lastname`, `birth`, `pesel`, `phone`, `address_city`, `address_zipcode`, `address_street`, `address_house`, `address_apartment`, `role_id`) 
              VALUES ('$email', '$firstname', '$lastname', '$birth', '$pesel', '$phone', '$address_city', '$address_zipcode', '$address_street', '$address_house', '$address_apartment', '$roleid');";
      
      if (mysqli_query($mysqli, $sqlcreateuser)) {
      } 
      else {
        $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
        $errordisplay = 1;
      }
    } 
  } //Edycja użytkownika/klienta
  else if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["editsubmit"])) {
    $id = htmlentities($_POST['editid']);
    $firstname = htmlentities($_POST['firstname']);
    $lastname = htmlentities($_POST['lastname']);
    $email = htmlentities($_POST['email']);
    $roleid = htmlentities($_POST['roleid']);
    $birth = htmlentities($_POST['birth']);
    $pesel = htmlentities($_POST['pesel']);
    $phone = htmlentities($_POST['phone']);
    $address_city = htmlentities($_POST['address_city']);
    $address_zipcode = htmlentities($_POST['address_zipcode']);
    $address_street = htmlentities($_POST['address_street']);
    $address_house = htmlentities($_POST['address_house']);
    $address_apartment = htmlentities($_POST['address_apartment']);
    if ($_POST['password'] != $_POST['repeatpassword']){
      $errormsg = "Hasła nie zgadzają się!";
      $errordisplay = 1;
    }
    else if ($_POST['password'] == $_POST['repeatpassword']){
      $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
      $sql = "UPDATE users SET firstname='$firstname', lastname='$lastname', role_id='$roleid', birth='$birth', pesel='$pesel', phone='$phone', address_city='$address_city', address_zipcode='$address_zipcode', address_street='$address_street', address_house='$address_house', address_apartment='$address_apartment', password_hash='$password_hash' WHERE id='$id'";
    
      if (mysqli_query($mysqli, $sql)) {
      } 
      else {
        $errormsg = "Wystąpił błąd podczas wykonywania operacji! Skontaktuj się z administratorem!";
        $errordisplay = 1;
      }
    }
    else {
      $sql = "UPDATE users SET firstname='$firstname', lastname='$lastname', role_id='$roleid', birth='$birth', pesel='$pesel', phone='$phone', address_city='$address_city', address_zipcode='$address_zipcode', address_street='$address_street', address_house='$address_house', address_apartment='$address_apartment' WHERE id='$id'";
    
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
  <?php if($_SESSION["user_role"] == 1) { ?><title>Elecar - Użytkownicy/Klienci</title><?php } ?>
  <?php if($_SESSION["user_role"] != 1) { ?><title>Elecar - Klienci</title><?php } ?>
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
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 fs-6" href="reservations.php">
                  <i class="bi bi-cart3"></i>
                  Wypożyczenia
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 fs-6" href="service.php">
                  <i class="bi bi-tools"></i>
                  Serwis
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 fs-6 active" aria-current="page" href="#">
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
          <?php if($_SESSION["user_role"] == 1) { ?><h1 class="h2">Użytkownicy/Klienci</h1> <?php } ?>
          <?php if($_SESSION["user_role"] != 1) { ?><h1 class="h2">Klienci</h1> <?php } ?>
          <div class="d-flex flex-wrap">
            <button type="button" class="btn btn-lg btn-outline-secondary mb-1" data-toggle="modal" data-target="#addClientModalCenter">Dodaj Klienta</button>
            <?php if($_SESSION["user_role"] == 1) { ?>
            <button type="button" class="btn btn-lg btn-outline-secondary mx-2 mb-1" data-toggle="modal" data-target="#addUserModalCenter">Dodaj Użytkownika</button><?php } ?>
            <?php if($_SESSION["user_role"] == 1) { ?>
            <form method="get">
              <select class="btn btn-lg btn-outline-secondary mb-1" name="view" onchange="this.form.submit();">  
                <option value="all" <?php echo (($userview  == "all")?"selected":"" );?>>Wszyscy Użytkownicy</option>  
                <option value="ekspedient" <?php echo (($userview  == "ekspedient")?"selected":"" );?>>Eskpedienci</option>  
                <option value="administrator" <?php echo (($userview  == "administrator")?"selected":"" );?>>Administratorzy</option>
                <option value="serwisant" <?php echo (($userview  == "serwisant")?"selected":"" );?>>Serwisanci</option>  
                <option value="klient" <?php echo (($userview  == "klient")?"selected":"" );?>>Klienci</option>     
              </select>
            </form>
            <?php } ?>
          </div>
          <?php if($_SESSION["user_role"] == 1) { ?>
          <div class="modal fade" id="addUserModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLongTitle"><img src="images/logo.png" alt="" width="40" height="40">  Dodaj Użytkownika</h5>
                </div>
                <form method="post" enctype="multipart/form-data">
                  <div class="modal-body">
                    <div class="form-group">
                      <label for="formGroupExampleInput">Imię</label>
                      <input type="text" class="form-control" pattern="[a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ]+" id="formGroupExampleInput" name="firstname"  required/>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Nazwisko</label>
                      <input type="text" class="form-control" pattern="[a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ]+" id="formGroupExampleInput" name="lastname"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">E-mail</label>
                      <input type="email" class="form-control" id="formGroupExampleInput" name="email"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Rola</label>
                      <select class="form-control" id="formGroupExampleInput" name="roleid" required>
                        <option value="1">Administrator</option>
                        <option value="2">Serwisant</option>
                        <option value="3">Ekspedient</option> 
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Data urodzenia</label>
                      <input type="date" class="form-control" id="formGroupExampleInput" name="birth"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Pesel</label>
                      <input type="text" maxlength="11" pattern="[0-9]+" class="form-control" id="formGroupExampleInput" name="pesel"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Telefon</label>
                      <input type="text" pattern="[0-9]{3}[0-9]{3}[0-9]{3}" class="form-control" id="formGroupExampleInput" name="phone"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Miasto</label>
                      <input type="text" class="form-control" pattern="^[a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ]+( [a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ]+)*$" id="formGroupExampleInput" name="address_city"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Kod pocztowy</label>
                      <input type="text" pattern="^\d{2}-\d{3}$" class="form-control" id="formGroupExampleInput" name="address_zipcode" required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Ulica</label>
                      <input type="text" class="form-control" pattern="^[a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ.]+( [a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ.]+)*$" id="formGroupExampleInput" name="address_street" required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Numer domu</label>
                      <input type="text" class="form-control" pattern="[a-zA-Z0-9]+" id="formGroupExampleInput" name="address_house"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Numer mieszkania</label>
                      <input type="text" class="form-control" pattern="[0-9]+" id="formGroupExampleInput" name="address_apartment"  >
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Hasło</label>
                      <input type="password" class="form-control" id="formGroupExampleInput" name="password"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Powtórz hasło</label>
                      <input type="password" class="form-control" id="formGroupExampleInput" name="repeatpassword"  required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Zamknij</button>
                    <button type="submit" value="Adduser" name="addusersubmit" class="btn btn-primary">Dodaj</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <?php } ?>
          <div class="modal fade" id="addClientModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLongTitle"><img src="images/logo.png" alt="" width="40" height="40">  Dodaj Klienta</h5>
                </div>
                <form method="post" enctype="multipart/form-data">
                  <div class="modal-body">
                    <div class="form-group">
                      <label for="formGroupExampleInput">Imię</label>
                      <input type="text" class="form-control" pattern="[a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ]+" id="formGroupExampleInput" name="firstname"  required/>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Nazwisko</label>
                      <input type="text" class="form-control" pattern="[a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ]+" id="formGroupExampleInput" name="lastname"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">E-mail</label>
                      <input type="email" class="form-control" id="formGroupExampleInput" name="email"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Rola</label>
                      <select class="form-control" id="formGroupExampleInput" name="roleid" required>
                        <option value="4">Klient</option>  
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Data urodzenia</label>
                      <input type="date" class="form-control" id="formGroupExampleInput" name="birth"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Pesel</label>
                      <input type="text" maxlength="11" pattern="[0-9]+" class="form-control" id="formGroupExampleInput" name="pesel"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Telefon</label>
                      <input type="text" maxlength="9" pattern="[0-9]{3}[0-9]{3}[0-9]{3}" class="form-control" id="formGroupExampleInput" name="phone"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Miasto</label>
                      <input type="text" class="form-control" pattern="^[a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ]+( [a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ]+)*$" id="formGroupExampleInput" name="address_city"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Kod pocztowy</label>
                      <input type="text" pattern="^\d{2}-\d{3}$" class="form-control" id="formGroupExampleInput" name="address_zipcode" required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Ulica</label>
                      <input type="text" class="form-control" pattern="^[a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ.]+( [a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ.]+)*$" id="formGroupExampleInput" name="address_street"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Numer domu</label>
                      <input type="text" class="form-control" pattern="[A-Za-z0-9]+" id="formGroupExampleInput" name="address_house"  required>
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Numer mieszkania</label>
                      <input type="text" class="form-control" pattern="[0-9]+" id="formGroupExampleInput" name="address_apartment">
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Zamknij</button>
                    <button type="submit" value="Adduser" name="addusersubmit" class="btn btn-primary">Dodaj</button>
                  </div>
                </form>
              </div>
            </div>
          </div> 
        </div>
        <?php if($errordisplay == 1) { ?> 
        <div class="alert alert-danger alert-dismissible fade show mt-1" role="alert">
          <?php echo $errormsg; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php } ?>
        <?php if ($userview  == "klient") { ?>
        <div class="d-flex justify-content-center">
          <h5>Wyszukaj Klienta</h5>
        </div>
        <div class="d-flex justify-content-center">
          <form method="get">
            <select class="btn btn-lg btn-outline-secondary mb-3" name="user_client" onchange="this.form.submit();">  
              <option value="all" >Wszyscy Klienci</option>
              <?php
                $sqlclientslist = "SELECT id, firstname, lastname, email FROM users WHERE isdeleted='0' AND role_id = 4 ORDER BY lastname";
                $resultclientslist = $mysqli->query($sqlclientslist);
                while($clientrow = $resultclientslist->fetch_assoc()) {  
              ?>
              <option <?php if (isset($_GET["user_client"])){ echo 'value="'.$clientrow["id"].'"'.(($_GET["user_client"]  == $clientrow["id"])?"selected":"" );?>>
                <?php echo $clientrow["lastname"]." ".$clientrow["firstname"]." - ".$clientrow["email"];?>
              </option> 
                <?php
                }
                else {
                  echo 'value="'.$clientrow["id"].'"';?>><?php echo $clientrow["lastname"]." ".$clientrow["firstname"]." - ".$clientrow["email"];?></option> 
              <?php }} ?> 
            </select>
          </form>
        </div>
        <?php } ?>
        <table class="table table-hover">
          <thead class="table-dark">
            <tr>
              <th scope="col">Dane Osobowe</th>
              <th scope="col">Adres</th>
              <th scope="col" class="buttonlist">Akcja</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              if ($userview  == "ekspedient") {
                $sql = sprintf("SELECT * FROM users WHERE role_id='3' AND isdeleted='0'");
              }
              else if ($userview  == "administrator") {
                $sql = sprintf("SELECT * FROM users WHERE role_id='1' AND isdeleted='0'");
              }
              else if ($userview  == "serwisant") {
                $sql = sprintf("SELECT * FROM users WHERE role_id='2' AND isdeleted='0'");
              }
              else if ($userview  == "klient" && (!isset($_GET["user_client"]) || $_GET["user_client"] == "all")) {
                $sql = sprintf("SELECT * FROM users WHERE role_id='4' AND isdeleted='0'");
              }
              else if (isset($_GET["user_client"])) {
                $sql = sprintf("SELECT * FROM users WHERE isdeleted='0' AND id=".$_GET["user_client"]);
              }
              else {
                $sql = sprintf("SELECT * FROM users WHERE isdeleted='0'");
              }
              $result = $mysqli->query($sql);
              while($row = $result->fetch_assoc()) {
                switch ($row['role_id']) {
                  case 1:
                  $role = "Administrator";
                  break;
                  case 2:
                  $role = "Serwisant";
                  break;
                  case 3:
                  $role = "Ekspedient";
                  break;
                  case 4:
                  $role = "Klient";
                  break;
                }
            ?>
            <tr>
              <td><?php echo "<b>Imię: </b>".$row['firstname']."</br><b>Nazwisko: </b>".$row['lastname']."</br><b>E-mail: </b>".$row['email']."</br><b>Rola: </b>".$role."</br><b>Data urodzenia: </b>".$row['birth']."</br><b>Pesel: </b>".$row['pesel']."</br><b>Telefon: </b>".$row['phone']; ?></td>
              <td><?php echo "<b>Miasto: </b>".$row['address_city']."</br><b>Kod pocztowy: </b>".$row['address_zipcode']."</br><b>Ulica: </b>".$row['address_street']."</br><b>Numer domu: </b>".$row['address_house']."</br><b>Numer mieszkania: </b>".$row['address_apartment']; ?></td>
              <td align="center" class="buttonlist"><button type='button' title="Edytuj" class='btn btn-warning mb-1' data-toggle='modal' <?php echo "data-target='#editModalCenter".$row["id"]."'"; ?>><i class="bi bi-pencil-square"></i></button>
              <a <?php echo 'href="clients.php?id='.$row["id"].'"'; ?> class='btn btn-danger' title="Usuń"><i class="bi bi-trash"></i></a></td>
            </tr>
            <tr class="flex-row d-md-none">
              <td align="center" colspan="2"><button type='button' title="Edytuj" class='btn btn-warning' data-toggle='modal' <?php echo "data-target='#editModalCenter".$row["id"]."'"; ?>><i class="bi bi-pencil-square"></i></button>
              <a <?php echo 'href="clients.php?id='.$row["id"].'"'; ?> class='btn btn-danger' title="Usuń"><i class="bi bi-trash"></i></a></td>
            </tr>
            <div class="modal fade" <?php echo 'id="editModalCenter'.$row["id"].'"'; ?> tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"><img src="images/logo.png" alt="" width="40" height="40">  Edytuj Użytkownika/Klienta</h5>
                  </div>
                  <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                      <div class="form-group">
                        <input type="hidden" class="form-control" id="formGroupExampleInput" name="editid"  <?php echo 'value="'.$row["id"].'"'; ?> readonly/>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Imię</label>
                        <input type="text" class="form-control" pattern="[a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ]+" id="formGroupExampleInput" name="firstname" <?php echo 'value="'.$row["firstname"].'"'; ?> required />
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Nazwisko</label>
                        <input type="text" class="form-control" pattern="[a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ]+" id="formGroupExampleInput" name="lastname" <?php echo 'value="'.$row["lastname"].'"'; ?> required >
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">E-mail</label>
                        <input type="email" class="form-control" id="formGroupExampleInput" name="email"  <?php echo 'value="'.$row["email"].'"'; ?> required >
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Rola</label>
                        <select class="form-control" id="formGroupExampleInput" name="roleid" required>
                          <?php if($_SESSION["user_role"] == 1){ ?><option value="1" <?php echo (($row['role_id']=='1')?'selected="selected"':""); ?>>Administrator</option><?php } ?>
                          <?php if($_SESSION["user_role"] == 1){ ?><option value="2" <?php echo (($row['role_id']=='2')?'selected="selected"':""); ?>>Serwisant</option><?php } ?>
                          <?php if($_SESSION["user_role"] == 1){ ?><option value="3" <?php echo (($row['role_id']=='3')?'selected="selected"':""); ?>>Ekspedient</option><?php } ?>
                          <option value="4" <?php echo (($row['role_id']=='4')?'selected="selected"':""); ?>>Klient</option> 
                        </select>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Data urodzenia</label>
                        <input type="date" class="form-control" id="formGroupExampleInput" name="birth" <?php echo 'value="'.$row["birth"].'"'; ?> required>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Pesel</label>
                        <input type="text" maxlength="11" pattern="[0-9]+" class="form-control" id="formGroupExampleInput" name="pesel" <?php echo 'value="'.$row["pesel"].'"'; ?> required>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Telefon</label>
                        <input type="text" maxlength="9" pattern="[0-9]{3}[0-9]{3}[0-9]{3}" class="form-control" id="formGroupExampleInput" name="phone" <?php echo 'value="'.$row["phone"].'"'; ?>  required>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Miasto</label>
                        <input type="text" class="form-control" pattern="^[a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ]+( [a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ]+)*$" id="formGroupExampleInput" name="address_city"  <?php echo 'value="'.$row["address_city"].'"'; ?> required>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Kod pocztowy</label>
                        <input type="text"  class="form-control" pattern="^\d{2}-\d{3}$" id="formGroupExampleInput" name="address_zipcode" <?php echo 'value="'.$row["address_zipcode"].'"'; ?> required>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Ulica</label>
                        <input type="text" class="form-control" pattern="^[a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ.]+( [a-zA-ZąęźżśóćńłĄĘŹŻŚÓĆŃŁ.]+)*$" id="formGroupExampleInput" name="address_street"  <?php echo 'value="'.$row["address_street"].'"'; ?> required>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Numer domu</label>
                        <input type="text" class="form-control" pattern="[a-zA-Z0-9]+" id="formGroupExampleInput" name="address_house"  <?php echo 'value="'.$row["address_house"].'"'; ?> required>
                      </div>
                      <div class="form-group">
                        <label for="formGroupExampleInput">Numer mieszkania</label>
                        <input type="text" class="form-control" pattern="[0-9]+" id="formGroupExampleInput" name="address_apartment"  <?php echo 'value="'.$row["address_apartment"].'"'; ?>>
                      </div>
                       <div class="form-group">
                      <label for="formGroupExampleInput">Hasło</label>
                      <input type="password" class="form-control" id="formGroupExampleInput" name="password">
                    </div>
                    <div class="form-group">
                      <label for="formGroupExampleInput">Powtórz hasło</label>
                      <input type="password" class="form-control" id="formGroupExampleInput" name="repeatpassword">
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