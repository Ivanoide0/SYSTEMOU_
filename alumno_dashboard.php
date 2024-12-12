<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'alumno') {
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

$user_id = $_SESSION['user_id'];

// Consulta para obtener las materias inscritas del alumno
$sql = "SELECT cursos.nombre AS materia, cursos.descripcion, profesores.especialidad AS profesor
        FROM inscripciones
        INNER JOIN cursos ON inscripciones.curso_id = cursos.id
        INNER JOIN profesores ON cursos.profesor_id = profesores.id
        WHERE inscripciones.alumno_id = (SELECT id FROM alumnos WHERE usuario_id = ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cursos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cursos[] = $row;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Alumno</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eafaf1;
            color: #2d6a4f;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
            max-width: 800px;
            margin: auto;
            background: #ffffff;
            border: 2px solid #95d5b2;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #40916c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
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
        <h1>Materias Inscritas</h1>
        <?php if (!empty($cursos)) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Descripción</th>
                        <th>Profesor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cursos as $curso) : ?>
                        <tr>
                            <td><?= htmlspecialchars($curso['materia']); ?></td>
                            <td><?= htmlspecialchars($curso['descripcion']); ?></td>
                            <td><?= htmlspecialchars($curso['profesor']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No estás inscrito en ninguna materia.</p>
        <?php endif; ?>
    </div>
</body>
</html>
