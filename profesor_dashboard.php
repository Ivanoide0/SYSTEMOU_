<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'profesor') {
    header("Location: index.php");
    exit();
}

// Conexión a la base de datos
$servername = "b9jf9sf7u2xhbt0xm51o-mysql.services.clever-cloud.com";
$username = "ujbuuqibgslsrzmv";
$password = "8KOYYYXR64QVJdkd0rZb";
$dbname = "b9jf9sf7u2xhbt0xm51o";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$profesor_id = $_SESSION['user_id'];

// Procesar formulario para agregar materia
if (isset($_POST['add_materia'])) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $sql = "INSERT INTO cursos (nombre, descripcion, profesor_id) VALUES (?, ?, (SELECT id FROM profesores WHERE usuario_id = ?))";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $nombre, $descripcion, $profesor_id);

    if ($stmt->execute()) {
        echo "Materia agregada con éxito.";
    } else {
        echo "Error al agregar la materia: " . $conn->error;
    }

    $stmt->close();
}

// Procesar formulario para asignar materia
if (isset($_POST['assign_materia'])) {
    $curso_id = $_POST['curso_id'];
    $alumno_id = $_POST['alumno_id'];

    $sql = "INSERT INTO inscripciones (curso_id, alumno_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $curso_id, $alumno_id);

    if ($stmt->execute()) {
        echo "Materia asignada al alumno con éxito.";
    } else {
        echo "Error al asignar la materia: " . $conn->error;
    }

    $stmt->close();
}

// Obtener las materias del profesor
$sql = "SELECT id, nombre, descripcion FROM cursos WHERE profesor_id = (SELECT id FROM profesores WHERE usuario_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profesor_id);
$stmt->execute();
$cursos_result = $stmt->get_result();
$cursos = $cursos_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener lista de alumnos
$sql = "SELECT alumnos.id, usuarios.nombre FROM alumnos INNER JOIN usuarios ON alumnos.usuario_id = usuarios.id";
$alumnos_result = $conn->query($sql);
$alumnos = $alumnos_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Profesor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eafaf1;
            color: #2d6a4f;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #ffffff;
            border: 2px solid #95d5b2;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
            color: #40916c;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input, textarea, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #95d5b2;
            border-radius: 5px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #95d5b2;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #40916c;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #f0fdf4;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Panel de Profesor</h1>

        <h2>Agregar Materia</h2>
        <form method="POST">
            <label for="nombre">Nombre de la materia:</label>
            <input type="text" name="nombre" required>

            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion" rows="3" required></textarea>

            <button type="submit" name="add_materia">Agregar Materia</button>
        </form>

        <h2>Asignar Materia a Alumno</h2>
        <form method="POST">
            <label for="curso_id">Materia:</label>
            <select name="curso_id" required>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?= $curso['id']; ?>"><?= htmlspecialchars($curso['nombre']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="alumno_id">Alumno:</label>
            <select name="alumno_id" required>
                <?php foreach ($alumnos as $alumno): ?>
                    <option value="<?= $alumno['id']; ?>"><?= htmlspecialchars($alumno['nombre']); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" name="assign_materia">Asignar Materia</button>
        </form>

        <h2>Materias Agregadas</h2>
        <?php if (!empty($cursos)) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cursos as $curso): ?>
                        <tr>
                            <td><?= htmlspecialchars($curso['nombre']); ?></td>
                            <td><?= htmlspecialchars($curso['descripcion']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No tienes materias agregadas aún.</p>
        <?php endif; ?>
    </div>
</body>
</html>
