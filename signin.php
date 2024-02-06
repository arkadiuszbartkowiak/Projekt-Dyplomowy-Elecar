<?php
  $is_invalid = false;
  session_start();
  if(isset($_SESSION["user_id"]) == true) {
    header("Location: index.php");
    exit;
  }
  if($_SERVER["REQUEST_METHOD"] === "POST") {
    $mysqli = require __DIR__ . "/database.php";
    $sql = sprintf("SELECT * FROM users WHERE email = '%s'", $mysqli->real_escape_string($_POST['email']));
    $result = $mysqli->query($sql);
    $user = $result->fetch_assoc();

    if($user) {
      if ($user["role_id"] == 4){
        $is_invalid = true;
      }
      else if(password_verify($_POST["password"], $user["password_hash"])) {
        session_start();
        session_regenerate_id();

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_role"] = $user["role_id"];
        $_SESSION["user_email"] = $user["email"];
        $_SESSION["user_firstname"] = $user["firstname"];
        $_SESSION["user_lastname"] = $user["lastname"];
        $_SESSION["user_ispasschanged"] = $user["ispasswordchanged"];

        if($_SESSION["user_ispasschanged"] == 0)
        {
          header("Location: settings.php");
          exit;
        }
        else
        {
         header("Location: index.php");
        exit;
        }
      }
    }
    $is_invalid = true;
  }
?>

<!DOCTYPE html>
<html lang="pl-PL">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Elecar - Logowanie</title>
  <link rel="icon" type="image/x-icon" href="/images/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" 
    integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous" />
  <link rel="stylesheet" type="text/css" href="styles/style.css" />
</head>
<body class="text-center">
  <div class="d-flex flex-column">
    <?php if($is_invalid): ?>
      <div class="validation-alert alert alert-danger d-flex align-items-center justify-content-center" role="alert">
        Nie udało się zalogować, spróbuj ponownie!
      </div>
    <?php endif; ?>
    <form class="form-signin" method="post">
      <img class="mb-4" src="images/logo.png" alt="" width="72" height="72">
      <h1 class="h3 mb-3 font-weight-normal">Proszę się zalogować</h1>
      <label for="inputEmail" class="sr-only mb-2">Adres Email</label>
      <input type="email" id="inputEmail" class="form-control" placeholder="E-mail" name="email" required autofocus>
      <label for="inputPassword" class="sr-only mb-2 mt-2">Hasło</label>
      <input type="password" id="inputPassword" class="form-control" placeholder="Hasło" name="password" required>
      <button class="btn btn-lg btn-primary btn-block mt-2" type="submit">Zaloguj się</button>
      <p class="mt-5 mb-3 text-muted">&copy; 2023</p>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
  </script>
</body>
</html>