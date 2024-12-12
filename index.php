<?php
// Conexión a la base de datos
$servername = "b9jf9sf7u2xhbt0xm51o-mysql.services.clever-cloud.com";
$username = "ujbuuqibgslsrzmv";
$password = "8KOYYYXR64QVJdkd0rZb";
$dbname = "b9jf9sf7u2xhbt0xm51o";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Procesar formulario de registro
if (isset($_POST['register'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
    $rol = $_POST['rol'];

    $sql = "INSERT INTO usuarios (nombre, email, contrasena, rol) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nombre, $email, $contrasena, $rol);

    if ($stmt->execute()) {
        echo "Registro exitoso. Ahora puedes iniciar sesión.";
    } else {
        echo "Error al registrar: " . $conn->error;
    }

    $stmt->close();
}

// Procesar formulario de inicio de sesión
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($contrasena, $user['contrasena'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['rol'] = $user['rol'];

            // Redirección según el rol del usuario
            if ($user['rol'] == 'profesor') {
                header("Location: profesor_dashboard.php");
            } else {
                header("Location: alumno_dashboard.php"); // Redirige al panel del alumno
            }
            exit();
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "No se encontró una cuenta con ese email.";
    }

    $stmt->close();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login y Registro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eafaf1;
            color: #2d6a4f;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #ffffff;
            border: 2px solid #95d5b2;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 90%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #40916c;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin: 10px 0 5px;
        }
        input, select, button {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #95d5b2;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background-color: #52b788;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #40916c;
        }
        .form-section {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-section">
            <h2>Registro</h2>
            <form method="POST">
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" required><br>

                <label for="email">Email:</label>
                <input type="email" name="email" required><br>

                <label for="contrasena">Contraseña:</label>
                <input type="password" name="contrasena" required><br>

                <label for="rol">Rol:</label>
                <select name="rol" required>
                    <option value="alumno">Alumno</option>
                    <option value="profesor">Profesor</option>
                </select><br>

                <button type="submit" name="register">Registrar</button>
            </form>
        </div>

        <div class="form-section">
            <h2>Inicio de Sesión</h2>
            <form method="POST">
                <label for="email">Email:</label>
                <input type="email" name="email" required><br>

                <label for="contrasena">Contraseña:</label>
                <input type="password" name="contrasena" required><br>

                <button type="submit" name="login">Iniciar Sesión</button>
            </form>
        </div>
    </div>
</body>
</html>
