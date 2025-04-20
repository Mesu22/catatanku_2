<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['theme_preference'])) {
    // Get theme from database if available
    require_once 'config/database.php';
    $stmt = $pdo->prepare("SELECT theme_preference FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $theme = $stmt->fetchColumn();
    $_SESSION['theme_preference'] = $theme ?: 'light';
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
    <link rel="stylesheet" href="public/css/sidebar/sidebarbaru.css">
    <link rel="stylesheet" href="public/css/beranda/search.css">
    <link rel="stylesheet" href="public/css/beranda/berandabaru.css">
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/beranda/berandabaru12.css">
</head>
<body class="<?php echo isset($_SESSION['theme_preference']) ? $_SESSION['theme_preference'] : 'light'; ?>">

    <?php include 'navbar.php'; ?>

    <?php include 'sidebar.php'; ?>

    <!-- Content -->
    <div class="content">
        <div class="container">
            <!-- Kolom kiri: To-Do dan Completed Tasks -->
            <div>
                <!-- To-Do List -->
                <div class="card">
                    <h3 class="welcome-message">👋 Welcome back, <?php echo $_SESSION['username']; ?></h3>
                    <h3>📌 To-Do</h3>
                    <button class="add-task-btn">
                        <span>➕</span> Add New Task
                    </button>
                </div>

                <!-- Completed Tasks -->
                <div class="card">
                    <h3>✅ Completed Task</h3>
                </div>
            </div>

            <!-- Kolom kanan: Task Status dan Tugas Terlewat -->
            <div>
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

                <!-- Overdue Tasks -->
                <div class="card">
                    <h3>⚠️ Tugas Terlewat</h3>
                    <div id="overdueTasks" class="task-list"></div>
                </div>
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
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
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
            const formData = new FormData(e.target);
            
            // Validasi tanggal
            const dueDate = new Date(formData.get('due_date'));
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // Jika tanggal jatuh tempo adalah hari ini atau masa depan
            // dan status bukan completed, set status sesuai pilihan user
            if (dueDate >= today) {
                // Biarkan status sesuai pilihan user
                const selectedStatus = formData.get('status');
                if (selectedStatus === 'overdue') {
                    // Jika user memilih overdue tapi tanggal belum lewat, 
                    // set ke not_started
                    formData.set('status', 'not_started');
                }
            }
            
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

        let tasks = []; // Tambahkan ini di luar semua fungsi

        function loadTasks() {
            fetch('tasks_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=read'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Tasks loaded:', data.data);
                    tasks = data.data;
                    updateTasksDisplay(tasks);
                    updateCharts(tasks);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function updateTasksDisplay(tasksToDisplay) {
            const todoContainer = document.querySelector('.container > div:first-child > .card:first-child');
            const completedContainer = document.querySelector('.container > div:first-child > .card:last-child');
            const overdueContainer = document.querySelector('#overdueTasks');
            
            let todoHTML = `
                <h3 class="welcome-message">👋 Welcome back, <?php echo $_SESSION['username']; ?></h3>
                <h3>📌 To-Do</h3>
                <button class="add-task-btn">
                    <span>➕</span> Add New Task
                </button>
            `;
            
            let overdueHTML = '';
            let completedHTML = `<h3>✅ Completed Task</h3>`;
            
            // Tambahkan pengecekan untuk hasil pencarian
            if (tasksToDisplay.length === 0) {
                const searchTerm = document.querySelector('.search-bar').value.trim();
                if (searchTerm) {
                    todoContainer.innerHTML = `
                        <h3 class="welcome-message">👋 Welcome back, <?php echo $_SESSION['username']; ?></h3>
                        <h3>📌 To-Do</h3>
                        <button class="add-task-btn">
                            <span>➕</span> Add New Task
                        </button>
                        <div class="no-results">
                            <p>Tidak ada tugas yang cocok dengan pencarian: "${searchTerm}"</p>
                        </div>
                    `;
                    completedContainer.innerHTML = `<h3>✅ Completed Task</h3>`;
                    overdueContainer.innerHTML = '';
                    return;
                }
            }
            
            tasksToDisplay.forEach(task => {
                const completedAt = task.completed_at 
                    ? new Date(task.completed_at).toLocaleString('id-ID', {
                        dateStyle: 'medium'
                    }) 
                    : '';
                
                const priorityIcons = {
                    'low': '🔵',
                    'medium': '🟡',
                    'high': '🔴'
                };
                
                const priority = task.priority || 'medium';
                
                const taskHTML = `
                    <div class="task task-${priority} ${task.status === 'overdue' ? 'overdue-task' : ''}" data-id="${task.id}">
                        <div class="task-header">
                            <strong>${task.title}</strong>
                            <span class="priority-badge ${priority}">${priorityIcons[priority]} ${priority.charAt(0).toUpperCase() + priority.slice(1)}</span>
                        </div>
                        <p>${task.description}</p>
                        <div class="task-meta">
                            <span class="task-date">📅 ${new Date(task.due_date).toLocaleDateString('id-ID')}</span>
                            <span class="status-${task.status}">${getStatusText(task.status)}</span>
                            ${task.completed_at ? `<span class="completed-date">✅ Selesai: ${completedAt}</span>` : ''}
                            ${task.overdue_at ? `<span class="overdue-date">⚠️ Terlewat: ${new Date(task.overdue_at).toLocaleDateString('id-ID')}</span>` : ''}
                        </div>
                        <div class="task-actions">
                            <button class="action-toggle" onclick="toggleActionMenu(this)"></button>
                            <div class="action-menu">
                                ${task.status !== 'completed' ? `
                                    <button class="action-item complete" onclick="completeTask(${task.id})">
                                        <i class="fas fa-check"></i> Complete
                                    </button>
                                ` : ''}
                                <button class="action-item edit" onclick="editTask(${task.id})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="action-item delete" onclick="deleteTask(${task.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                // Ubah logika pengecekan status tugas
                const taskDate = new Date(task.due_date);
                taskDate.setHours(0, 0, 0, 0);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (task.status === 'completed') {
                    completedHTML += taskHTML;
                } else {
                    const taskDate = new Date(task.due_date);
                    taskDate.setHours(0, 0, 0, 0);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    if (taskDate < today) {
                        // Jika tanggal sudah lewat, masukkan ke overdue
                        overdueHTML += taskHTML;
                    } else {
                        // Jika tanggal belum lewat, masukkan ke todo
                        todoHTML += taskHTML;
                    }
                }
            });
            
            // Debug log untuk melihat konten overdue
            console.log('Overdue HTML:', overdueHTML);
            
            todoContainer.innerHTML = todoHTML;
            overdueContainer.innerHTML = overdueHTML || '<p class="no-tasks-message">Tidak ada tugas yang terlewat</p>';
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
            
            // Terapkan filter pencarian jika ada nilai di search bar
            const searchTerm = document.querySelector('.search-bar').value.toLowerCase().trim();
            if (searchTerm) {
                filterTasks(searchTerm);
            }
        }

        function editTask(taskId) {
            // Cari task dengan ID yang sesuai
            const task = tasks.find(t => t.id === taskId);
            
            if (task) {
                // Isi form dengan data task yang ditemukan
                document.getElementById('taskId').value = task.id;
                document.getElementById('title').value = task.title;
                document.getElementById('description').value = task.description;
                document.getElementById('due_date').value = formatDate(task.due_date);
                document.getElementById('priority').value = task.priority;
                document.getElementById('status').value = task.status;
                
                // Set action ke update dan tampilkan modal
                document.querySelector('input[name="action"]').value = 'update';
                document.getElementById('taskModal').style.display = 'block';
            } else {
                console.error('Task not found');
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        }

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

        // Tambahkan event listener untuk pencarian pada input search-bar
        const searchBar = document.querySelector('.search-bar');
        const clearButton = document.querySelector('.search-clear');

        // Tampilkan tombol clear ketika user mengetik
        searchBar.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            
            if (searchTerm.length > 0) {
                clearButton.style.display = 'flex';
            } else {
                clearButton.style.display = 'none';
            }
        });

        // Fungsi untuk menghapus isi pencarian
        clearButton.addEventListener('click', function() {
            searchBar.value = '';
            clearButton.style.display = 'none';
            filterTasks('');
            searchBar.focus();
        });

        // Tambahkan animasi ketika search bar difokuskan
        searchBar.addEventListener('focus', function() {
            document.querySelector('.search-wrapper').classList.add('active');
        });

        searchBar.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                document.querySelector('.search-wrapper').classList.remove('active');
            }
        });

        // Pastikan fungsi filter task dipanggil ketika tombol enter ditekan
        searchBar.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterTasks(this.value.toLowerCase().trim());
            }
        });

        // Tambahkan fungsi initializeSearch
        function initializeSearch() {
            const searchBar = document.querySelector('.search-bar');
            const clearButton = document.querySelector('.search-clear');
            
            if (!searchBar || !clearButton) {
                console.error('Search elements not found');
                return;
            }

            // Event listener untuk input pencarian
            searchBar.addEventListener('input', function() {
                const searchTerm = this.value.trim().toLowerCase();
                clearButton.style.display = searchTerm.length > 0 ? 'block' : 'none';
                filterTasks(searchTerm);
            });

            // Event listener untuk tombol clear
            clearButton.addEventListener('click', function() {
                searchBar.value = '';
                this.style.display = 'none';
                filterTasks('');
                searchBar.focus();
            });

            // Event listener untuk tombol Enter
            searchBar.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterTasks(this.value.toLowerCase().trim());
                }
            });
        }

        // Perbarui fungsi filterTasks
        function filterTasks(searchTerm) {
            if (!tasks || !Array.isArray(tasks)) {
                return;
            }
            
            searchTerm = searchTerm.toLowerCase().trim();
            
            // Jika searchTerm kosong, tampilkan semua tasks
            if (!searchTerm) {
                updateTasksDisplay(tasks);
                return;
            }

            // Filter hanya berdasarkan title dan description
            const filteredTasks = tasks.filter(task => {
                const title = task.title ? task.title.toLowerCase() : '';
                const description = task.description ? task.description.toLowerCase() : '';
                
                // Cek exact match terlebih dahulu untuk performa lebih baik
                if (title === searchTerm || description === searchTerm) {
                    return true;
                }
                
                // Kemudian cek includes
                return title.includes(searchTerm) || description.includes(searchTerm);
            });

            updateTasksDisplay(filteredTasks);
        }

        // Tambahkan fungsi helper untuk mendapatkan teks status
        function getStatusText(status) {
            switch(status) {
                case 'overdue':
                    return 'Tugas Terlewat';
                case 'completed':
                    return 'Selesai';
                case 'in_progress':
                    return 'Sedang Dikerjakan';
                case 'not_started':
                    return 'Belum Dimulai';
                default:
                    return 'Belum Dimulai';
            }
        }

        // Tambahkan fungsi checkOverdueTasks
        function checkOverdueTasks() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            tasks.forEach(task => {
                // Skip jika task sudah completed
                if (task.status === 'completed') return;
                
                // Skip jika tidak ada due_date
                if (!task.due_date) return;
                
                const taskDate = new Date(task.due_date);
                taskDate.setHours(0, 0, 0, 0);

                // Jika tanggal sudah lewat dan status bukan completed,
                // update ke overdue tanpa mempertimbangkan in_progress
                if (taskDate < today && task.status !== 'completed') {
                    const formData = new FormData();
                    formData.append('action', 'update_status');
                    formData.append('id', task.id);
                    formData.append('status', 'overdue');
                    formData.append('user_id', <?php echo $_SESSION['user_id']; ?>);

                    fetch('tasks_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadTasks();
                        }
                    })
                    .catch(error => console.error('Error updating overdue status:', error));
                }
            });
        }

        // Tambahkan CSS untuk styling tugas terlewat
        const style = document.createElement('style');
        style.textContent = `
            .overdue-task {
                border-left: 4px solid #dc3545 !important;
                background-color: rgba(220, 53, 69, 0.1);
            }

            .status-overdue {
                background-color: #dc3545;
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 0.85em;
            }

            .overdue-date {
                color: #dc3545;
                font-weight: 500;
                font-size: 0.85em;
            }

            #overdueTasks {
                margin-top: 15px;
            }

            #overdueTasks .no-tasks-message {
                text-align: center;
                color: #666;
                padding: 20px;
            }
        `;
        document.head.appendChild(style);

        document.addEventListener('DOMContentLoaded', function() {
            loadTasks();
            initializeSearch();
        });

        loadTasks();

        document.addEventListener('DOMContentLoaded', () => {
            // Initialize theme
            const savedTheme = localStorage.getItem('theme') || document.body.className || 'light';
            document.body.className = savedTheme;
            document.querySelector('.navbar').className = `navbar ${savedTheme}`;
            document.querySelector('.sidebar').className = `sidebar ${savedTheme}`;
            
            // Observe theme changes
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class') {
                        const theme = document.body.className;
                        document.querySelector('.navbar').className = `navbar ${theme}`;
                        document.querySelector('.sidebar').className = `sidebar ${theme}`;
                    }
                });
            });

            observer.observe(document.body, {
                attributes: true
            });
        });

        function toggleActionMenu(button) {
            // Tutup semua menu yang terbuka
            document.querySelectorAll('.action-menu.show').forEach(menu => {
                if (menu !== button.nextElementSibling) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle menu yang diklik
            const menu = button.nextElementSibling;
            menu.classList.toggle('show');
        }

        // Tutup menu jika user mengklik di luar menu
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.task-actions')) {
                document.querySelectorAll('.action-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });

        // Tambahkan fungsi completeTask
        function completeTask(taskId) {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('id', taskId);
            formData.append('status', 'completed');
            formData.append('user_id', <?php echo $_SESSION['user_id']; ?>);

            fetch('tasks_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Task marked as completed!');
                    loadTasks();
                } else {
                    alert(data.message || 'Failed to complete task');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to complete task');
            });
        }

        // Tambahkan CSS untuk tombol Complete
        const additionalStyle = document.createElement('style');
        additionalStyle.textContent = `
            .action-item.complete {
                background-color: #10B981;
                color: white;
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 12px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s ease;
                width: 100%;
                text-align: left;
                margin-bottom: 4px;
            }

            .action-item.complete:hover {
                background-color: #059669;
                transform: translateY(-1px);
            }

            .action-menu {
                position: absolute;
                right: 0;
                top: 100%;
                background: var(--component-bg);
                border: 1px solid var(--border-color);
                border-radius: 8px;
                padding: 8px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                display: none;
                z-index: 1000;
                min-width: 150px;
            }

            .action-menu.show {
                display: block;
                animation: fadeIn 0.2s ease;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-5px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(additionalStyle);

        // Tambahkan CSS untuk styling hasil pencarian
        const searchStyles = document.createElement('style');
        searchStyles.textContent = `
            .task {
                opacity: 1;
                transition: opacity 0.3s ease;
            }

            .search-highlight {
                background-color: rgba(99, 102, 241, 0.2);
                padding: 2px;
                border-radius: 2px;
            }

            .search-no-results {
                text-align: center;
                padding: 20px;
                color: var(--text-color);
                background: var(--component-bg);
                border-radius: 8px;
                margin: 20px 0;
            }
        `;
        document.head.appendChild(searchStyles);

        // Inisialisasi pencarian saat dokumen dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const searchBar = document.querySelector('.search-bar');
            const clearButton = document.querySelector('.search-clear');
            
            if (searchBar && clearButton) {
                // Event untuk input pencarian
                searchBar.addEventListener('input', function() {
                    const searchTerm = this.value.trim();
                    clearButton.style.display = searchTerm.length > 0 ? 'block' : 'none';
                    filterTasks(searchTerm);
                });

                // Event untuk tombol clear
                clearButton.addEventListener('click', function() {
                    searchBar.value = '';
                    this.style.display = 'none';
                    loadTasks(); // Muat ulang semua tugas
                    searchBar.focus();
                });

                // Event untuk tombol Enter
                searchBar.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        filterTasks(this.value.trim());
                    }
                });
            }
        });
    </script>

    <script src="public/js/theme.js"></script>

</body>
</html>
