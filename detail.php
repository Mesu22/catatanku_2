<?php
require_once 'config/database.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: kalender.php");
    exit();
}

$task_id = $_GET['id'];

$task_query = "SELECT * FROM tasks WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($task_query);
$stmt->execute([$task_id, $_SESSION['user_id']]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    header("Location: kalender.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas - CatatanKu</title>
    <link rel="stylesheet" href="public/css/kalender/kalender.css">
    <style>
        .detail-container {
            max-width: 900px;
            margin: 120px auto 40px;
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f0f0f0;
        }

        .detail-header h1 {
            color: #2c3e50;
            font-size: 32px;
            font-weight: 700;
            position: relative;
        }

        .detail-header h1:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: #3498db;
            border-radius: 2px;
        }

        .detail-content {
            margin: 30px 0;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }

        .detail-field {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .detail-field:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .detail-field label {
            font-weight: 600;
            color: #2c3e50;
            display: block;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .detail-field p {
            color: #34495e;
            font-size: 16px;
            line-height: 1.6;
            margin: 0;
        }

        .detail-actions {
            margin-top: 40px;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-edit:before {
            content: '✏️';
        }

        .btn-delete:before {
            content: '🗑️';
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .status-not-started {
            background: #ffeaa7;
            color: #d35400;
        }

        .status-in-progress {
            background: #81ecec;
            color: #00b894;
        }

        .status-completed {
            background: #a8e6cf;
            color: #27ae60;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="detail-container">
        <div class="detail-header">
            <h1>Detail Tugas</h1>
            <div class="detail-actions">
                <button class="btn btn-edit" onclick="window.location.href='edit.php?id=<?php echo $task['id']; ?>'">Edit</button>
                <button class="btn btn-delete" onclick="if(confirm('Apakah anda yakin ingin menghapus tugas ini?')) window.location.href='delete_task.php?id=<?php echo $task['id']; ?>'">Hapus</button>
            </div>
        </div>

        <div class="detail-content">
            <div class="detail-field">
                <label>Judul</label>
                <p><?php echo htmlspecialchars($task['title']); ?></p>
            </div>

            <div class="detail-field">
                <label>Status</label>
                <p>
                    <span class="status-badge <?php 
                        echo 'status-' . $task['status'];
                    ?>">
                    <?php 
                        switch($task['status']) {
                            case 'not_started':
                                echo '🔵 Belum Dimulai';
                                break;
                            case 'in_progress':
                                echo '🟡 Sedang Berjalan';
                                break;
                            case 'completed':
                                echo '🟢 Selesai';
                                break;
                            default:
                                echo $task['status'];
                        }
                    ?>
                    </span>
                </p>
            </div>

            <div class="detail-field">
                <label>Tanggal</label>
                <p>📅 <?php echo date('d F Y', strtotime($task['due_date'])); ?></p>
            </div>

            <div class="detail-field">
                <label>Waktu</label>
                <p>⏰ <?php echo date('H:i', strtotime($task['due_time'])); ?></p>
            </div>

            <div class="detail-field" style="grid-column: 1 / -1;">
                <label>Deskripsi</label>
                <p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('expanded');
            document.querySelector('.content').classList.toggle('shifted');
        });
    </script>
</body>
</html>
