<?php
$conn = new mysqli('localhost', 'root', '', 'tasksync');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'add':
        $title = trim($_POST['title'] ?? '');
        if ($title !== '') {
            $max = $conn->query("SELECT COALESCE(MAX(sr_no),0)+1 AS n FROM tasks")->fetch_assoc()['n'];
            $stmt = $conn->prepare("INSERT INTO tasks (sr_no, title) VALUES (?, ?)");
            $stmt->bind_param("is", $max, $title);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: index.html");
        exit;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $conn->query("DELETE FROM tasks WHERE id=$id");
            $res = $conn->query("SELECT id FROM tasks ORDER BY sr_no ASC");
            $i = 1;
            while ($row = $res->fetch_assoc()) {
                $conn->query("UPDATE tasks SET sr_no=$i WHERE id={$row['id']}");
                $i++;
            }
        }
        echo json_encode(['success' => true]);
        exit;

    case 'update_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'not_started';
        $allowed = ['not_started', 'pending', 'completed'];
        if ($id > 0 && in_array($status, $allowed)) {
            $stmt = $conn->prepare("UPDATE tasks SET status=? WHERE id=?");
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(['success' => true]);
        exit;

    case 'fetch':
        $col = $_GET['sort'] ?? 'sr_no';
        $dir = strtoupper($_GET['dir'] ?? 'ASC');
        $okCols = ['sr_no', 'title', 'status'];
        $okDirs = ['ASC', 'DESC'];
        if (!in_array($col, $okCols)) $col = 'sr_no';
        if (!in_array($dir, $okDirs)) $dir = 'ASC';
        $res = $conn->query("SELECT * FROM tasks ORDER BY $col $dir");
        $tasks = [];
        while ($row = $res->fetch_assoc()) $tasks[] = $row;
        header('Content-Type: application/json');
        echo json_encode($tasks);
        exit;
}
$conn->close();
?>