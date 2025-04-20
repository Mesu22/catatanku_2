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
            max-width: 1000px;
            margin: 100px auto;
            background: linear-gradient(145deg, #ffffff, #f5f7fa);
            padding: 50px;
            border-radius: 30px;
            box-shadow: 20px 20px 60px #d9d9d9, -20px -20px 60px #ffffff;
        }

        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 25px;
            border-bottom: 2px solid rgba(52, 152, 219, 0.2);
        }

        .detail-header h1 {
            color: #2c3e50;
            font-size: 36px;
            font-weight: 800;
            background: linear-gradient(45deg, #3498db, #2980b9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .detail-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }

        .detail-field {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(52, 152, 219, 0.1);
        }

        .detail-field:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 30px rgba(52, 152, 219, 0.2);
            border-color: #3498db;
        }

        .detail-field label {
            font-weight: 700;
            color: #34495e;
            font-size: 20px;
            margin-bottom: 15px;
            display: block;
            letter-spacing: 0.5px;
        }

        .detail-field p {
            color: #2c3e50;
            font-size: 18px;
            line-height: 1.8;
            margin: 0;
        }

        .detail-actions {
            display: flex;
            gap: 20px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-edit {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .btn:active {
            transform: translateY(1px);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .status-not_started {
            background: linear-gradient(45deg, #ffeaa7, #fdcb6e);
            color: #d35400;
        }

        .status-in_progress {
            background: linear-gradient(45deg, #81ecec, #00cec9);
            color: #006266;
        }

        .status-completed {
            background: linear-gradient(45deg, #a8e6cf, #55efc4);
            color: #00b894;
        }

        .description-field {
            grid-column: 1 / -1;
            background: rgba(255, 255, 255, 0.95);
            padding: 35px;
            border-radius: 25px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .detail-container {
                margin: 20px;
                padding: 30px;
            }

            .detail-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .btn {
                padding: 12px 20px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="detail-container">
        <div class="detail-header">
            <h1>Detail Tugas</h1>
            <div class="detail-actions">
                <button class="btn btn-edit" onclick="window.location.href='edit.php?id=<?php echo $task['id']; ?>'">✏️ Edit</button>
                <button class="btn btn-delete" onclick="if(confirm('Apakah anda yakin ingin menghapus tugas ini?')) window.location.href='delete_task.php?id=<?php echo $task['id']; ?>'">🗑️ Hapus</button>
            </div>
        </div>

        <div class="detail-content">
            <div class="detail-field">
                <label>📝 Judul</label>
                <p><?php echo htmlspecialchars($task['title']); ?></p>
            </div>

            <div class="detail-field">
                <label>🔄 Status</label>
                <p>
                    <span class="status-badge status-<?php echo $task['status']; ?>">
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
                <label>📅 Tanggal</label>
                <p><?php echo date('d F Y', strtotime($task['due_date'])); ?></p>
            </div>

            <div class="detail-field">
                <label>⏰ Waktu</label>
                <p><?php echo date('H:i', strtotime($task['due_time'])); ?></p>
            </div>

            <div class="description-field">
                <label>📋 Deskripsi</label>
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
