<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Mark task as completed
if (isset($_POST['mark_completed'])) {
    $task_id = $_POST['task_id'];
    $user_id = $_SESSION['user_id'];
    
    // Update task status to completed and set completion time
    $sql = "UPDATE tasks SET status='completed', completed_at=NOW() WHERE id=? AND user_id=?";
    require_once 'config/database.php';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $task_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Task marked as completed!";
    } else {
        $_SESSION['error'] = "Error marking task as completed";
    }
    
    header("Location: beranda.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CatatanKu</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="public/css/beranda/beranda.css">
    <link rel="stylesheet" href="public/css/sidebar/sidebar.css">
    
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <img src="img/logo/logo mesa.png" alt="CatatanKu Logo">
        <input type="text" class="search-bar" placeholder="Cari tugas anda...">
    </div>

    <?php include 'sidebar.php'; ?>

    <!-- Content -->
    <div class="content">
        <div class="container">
            <!-- To-Do List -->
            <div class="card">
                <h3 class="welcome-message">👋 Welcome back, <?php echo $_SESSION['username']; ?></h3>
                <h3>📌 To-Do</h3>
                <button class="add-task-btn">
                    <span>➕</span> Add New Task
                </button>
            </div>

            <!-- Task Status -->
            <div class="card">
                <h3>📊 Task Status</h3>
                <div class="chart-container">
                    <div class="chart-wrapper">
                        <canvas id="completedChart"></canvas>
                        <div class="chart-title">Completed</div>
                        <div class="chart-count" id="completedCount">0 Tasks (0%)</div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="inProgressChart"></canvas>
                        <div class="chart-title">In Progress</div>
                        <div class="chart-count" id="inProgressCount">0 Tasks (0%)</div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="notStartedChart"></canvas>
                        <div class="chart-title">Not Started</div>
                        <div class="chart-count" id="notStartedCount">0 Tasks (0%)</div>
                    </div>
                </div>
            </div>

            <!-- Completed Tasks -->
            <div class="card">
                <h3>✅ Completed Task</h3>
            </div>
        </div>
    </div>

    <!-- Success Notification -->
    <div id="successNotification" class="notification">
        ✅ Task successfully saved!
    </div>

    <!-- Add this HTML for the task form modal -->
    <div id="taskModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add New Task</h2>
            <form id="taskForm" class="modern-form">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="id" id="taskId">
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                
                <div class="form-group">
                    <label for="title">Task Title</label>
                    <input type="text" id="title" name="title" required placeholder="What needs to be done?">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required placeholder="Add more details about this task..." rows="4"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group half">
                        <label for="due_date">Due Date</label>
                        <input type="date" id="due_date" name="due_date" required>
                    </div>

                    <div class="form-group half">
                        <label for="due_time">Time</label>
                        <input type="time" id="due_time" name="due_time" required value="00:00">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('taskModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn-save">Save Task</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="delete-modal">
        <div class="delete-modal-content">
            <h3>Konfirmasi Hapus</h3>
            <p>Apakah Anda yakin ingin menghapus tugas ini?</p>
            <div class="delete-modal-buttons">
                <button class="btn-yes" onclick="confirmDelete()">Ya</button>
                <button class="btn-no" onclick="cancelDelete()">Tidak</button>
            </div>
        </div>
    </div>

    <script>
        // Add sidebar toggle functionality
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('expanded');
            document.querySelector('.content').classList.toggle('shifted');
        });

        function createChart(canvasId, percentage, color) {
            let ctx = document.getElementById(canvasId).getContext("2d");
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [percentage, 100 - percentage],
                        backgroundColor: [color, "#e6e6e6"],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        tooltip: { enabled: false },
                        legend: { display: false }
                    }
                }
            });
            
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font = '12px Arial';
            ctx.fillStyle = '#333';
            ctx.fillText(percentage + '%', 40, 40);
        }

        function updateCharts(tasks) {
            let total = tasks.length;
            let completed = 0;
            let inProgress = 0;
            let notStarted = 0;

            tasks.forEach(task => {
                if (task.status === 'completed') completed++;
                else if (task.status === 'in_progress') inProgress++;
                else if (task.status === 'not_started') notStarted++;
            });

            let completedPercentage = total > 0 ? Math.round((completed / total) * 100) : 0;
            let inProgressPercentage = total > 0 ? Math.round((inProgress / total) * 100) : 0;
            let notStartedPercentage = total > 0 ? Math.round((notStarted / total) * 100) : 0;

            createChart("completedChart", completedPercentage, "#28a745");
            createChart("inProgressChart", inProgressPercentage, "#007bff");
            createChart("notStartedChart", notStartedPercentage, "#dc3545");

            // Update count displays
            document.getElementById('completedCount').textContent = `${completed} Tasks (${completedPercentage}%)`;
            document.getElementById('inProgressCount').textContent = `${inProgress} Tasks (${inProgressPercentage}%)`;
            document.getElementById('notStartedCount').textContent = `${notStarted} Tasks (${notStartedPercentage}%)`;
        }

        document.querySelector('.add-task-btn').addEventListener('click', () => {
            document.getElementById('taskModal').style.display = 'block';
            document.getElementById('taskForm').reset();
            document.querySelector('input[name="action"]').value = 'create';
        });

        document.querySelector('.close').addEventListener('click', () => {
            document.getElementById('taskModal').style.display = 'none';
        });

        function showNotification() {
            const notification = document.getElementById('successNotification');
            notification.style.display = 'block';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        document.getElementById('taskForm').addEventListener('submit', (e) => {
            e.preventDefault();
            document.getElementById('taskModal').style.display = 'none';
            showNotification();
        });

        function loadTasks() {
            fetch('tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=read'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateTasksDisplay(data.data);
                    updateCharts(data.data);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function updateTasksDisplay(tasks) {
            const todoContainer = document.querySelector('.card:first-of-type');
            const completedContainer = document.querySelector('.card:nth-of-type(3)');
            
            let todoHTML = `
                <h3 class="welcome-message">👋 Welcome back, <?php echo $_SESSION['username']; ?></h3>
                <h3>📌 To-Do</h3>
                <button class="add-task-btn">
                    <span>➕</span> Add New Task
                </button>
            `;
            
            let completedHTML = `<h3>✅ Completed Task</h3>`;
            
            tasks.forEach(task => {
                const dueTime = task.due_time 
                    ? task.due_time.substring(0, 5)  // Take only HH:mm part
                    : '00:00';
                
                const completedAt = task.completed_at 
                    ? new Date(task.completed_at).toLocaleString('id-ID', {
                        dateStyle: 'medium',
                        timeStyle: 'short'
                    }) 
                    : '';
                
                const taskHTML = `
                    <div class="task" data-id="${task.id}">
                        <strong>${task.title}</strong>
                        <p>${task.description}</p>
                        <div class="status">
                            <span class="${task.status.replace('_', '-')}">${task.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                            <small>📅 ${new Date(task.due_date).toLocaleDateString('id-ID')} ⏰ ${dueTime}</small>
                            ${task.status === 'completed' ? `<small>✅ Completed: ${completedAt}</small>` : ''}
                            ${task.status !== 'completed' ? `
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="task_id" value="${task.id}">
                                <button type="submit" name="mark_completed" class="complete-btn">✓ Complete</button>
                            </form>
                            ` : ''}
                        </div>
                        <div class="menu-dots">⋮</div>
                        <div class="dropdown-menu">
                            <button onclick="editTask(${task.id}, '${task.title}', '${task.description}', '${task.due_date}', '${task.due_time || ''}', '${task.status}')">Edit</button>
                            <button onclick="deleteTask(${task.id})">Delete</button>
                        </div>
                    </div>
                `;
                
                if (task.status === 'completed') {
                    completedHTML += taskHTML;
                } else {
                    todoHTML += taskHTML;
                }
            });
            
            todoContainer.innerHTML = todoHTML;
            completedContainer.innerHTML = completedHTML;
            
            // Add event listeners for menu dots
            document.querySelectorAll('.menu-dots').forEach(dot => {
                dot.addEventListener('click', function() {
                    // Hide all other dropdown menus first
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        menu.style.display = 'none';
                    });
                    // Show this dropdown menu
                    this.nextElementSibling.style.display = 'block';
                });
            });
            
            document.querySelector('.add-task-btn').addEventListener('click', () => {
                document.getElementById('taskModal').style.display = 'block';
                document.getElementById('taskForm').reset();
                document.querySelector('input[name="action"]').value = 'create';
            });
        }

        function editTask(id, title, description, dueDate, dueTime, status) {
            document.getElementById('taskId').value = id;
            document.getElementById('title').value = title;
            document.getElementById('description').value = description;
            document.getElementById('due_date').value = dueDate;
            document.getElementById('due_time').value = dueTime ? dueTime.substring(0, 5) : '00:00';
            document.getElementById('status').value = status;
            document.querySelector('input[name="action"]').value = 'update';
            document.getElementById('taskModal').style.display = 'block';
        }

        document.getElementById('taskForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            fetch('tasks.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('taskModal').style.display = 'none';
                    showNotification();
                    loadTasks();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        let taskToDelete = null;

        function deleteTask(taskId) {
            taskToDelete = taskId;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function confirmDelete() {
            if (taskToDelete) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', taskToDelete);
                
                fetch('tasks.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadTasks();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            document.getElementById('deleteModal').style.display = 'none';
            taskToDelete = null;
        }

        function cancelDelete() {
            document.getElementById('deleteModal').style.display = 'none';
            taskToDelete = null;
        }

        // Close dropdown menus when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.matches('.menu-dots')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.style.display = 'none';
                });
            }
        });

        loadTasks();
    </script>

</body>
</html>
