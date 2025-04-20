<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check theme preference
if (!isset($_SESSION['theme_preference'])) {
    // Get theme from database if available
    require_once 'config/database.php';
    $stmt = $pdo->prepare("SELECT theme_preference FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $theme = $stmt->fetchColumn();
    $_SESSION['theme_preference'] = $theme ?: 'light';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar To-Do List</title>
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/sidebar/sidebarbaru.css">
    <link rel="stylesheet" href="public/css/kalender/kalenderbaru12.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="<?php echo isset($_SESSION['theme_preference']) ? $_SESSION['theme_preference'] : 'light'; ?>">
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="calendar-container">
            <!-- Calendar Side -->
            <div class="calendar-left-side">
                <div class="calendar-header">
                    <div class="calendar-title">
                        <h2>Calendar Tasks</h2>
                        <div class="current-month" id="currentMonth"></div>
                </div>
                <div class="calendar-nav">
                    <button class="nav-btn" id="prevMonth">
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <button class="nav-btn" id="nextMonth">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="calendar-section">
                <div class="calendar-grid" id="calendarGrid"></div>
            </div>
        </div>

        <!-- Task List Side -->
        <div class="task-list-section">
            <div class="task-list-header">
                <h3>Tasks</h3>
                <button class="filter-button" onclick="openAddTaskModal()">Add New Task</button>
            </div>
            <div class="filter-controls">
                <button class="filter-button active" data-filter="all">All</button>
                <button class="filter-button" data-filter="not_started">Not Started</button>
                <button class="filter-button" data-filter="in_progress">In Progress</button>
                <button class="filter-button" data-filter="completed">Completed</button>
                <button class="filter-button" data-filter="overdue">Tugas Terlewat</button>
            </div>
            <div class="task-list" id="taskList"></div>
        </div>
    </div>

    <!-- Add/Edit Task Modal -->
    <div id="taskModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">Add New Task</h3>
            <form id="taskForm">
                <input type="hidden" id="taskId">
                <div class="form-group">
                    <label for="taskTitle">Title:</label>
                    <input type="text" id="taskTitle" required>
                </div>
                <div class="form-group">
                    <label for="taskDescription">Description:</label>
                    <textarea id="taskDescription" required></textarea>
                </div>
                <div class="form-group">
                    <label for="taskDate">Date:</label>
                    <input type="date" id="taskDate" required>
                </div>
                <div class="form-group">
                    <label for="taskPriority">Priority:</label>
                    <select id="taskPriority" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="taskStatus">Status:</label>
                    <select id="taskStatus" required>
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="overdue">Tugas Terlewat</option>
                </select>
                </div>
                <div class="form-actions">
                    <button type="submit">Save</button>
                    <button type="button" onclick="closeTaskModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Confirmation Modal -->
    <div id="editConfirmModal" class="modal">
        <div class="modal-content">
            <h3>Konfirmasi Edit</h3>
            <p>Apakah Anda yakin ingin mengubah task ini?</p>
            <div class="modal-actions">
                <button onclick="confirmEdit()" class="btn-confirm">Ya</button>
                <button onclick="cancelEdit()" class="btn-cancel">Tidak</button>
            </div>
        </div>
    </div>

    <!-- Edit Task Popup -->
    <div id="editTaskPopup" class="modal">
        <div class="modal-content edit-popup">
            <div class="popup-header">
                <h3>Edit Task</h3>
                <span class="close-popup" onclick="closeEditPopup()">&times;</span>
            </div>
            <form id="editTaskForm">
                <input type="hidden" id="editTaskId">
                <div class="form-group">
                    <label for="editTitle">Title:</label>
                    <input type="text" id="editTitle" required>
                </div>
                <div class="form-group">
                    <label for="editDescription">Description:</label>
                    <textarea id="editDescription" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editDueDate">Due Date:</label>
                        <input type="date" id="editDueDate" required>
                    </div>
                    <div class="form-group">
                        <label for="editPriority">Priority:</label>
                        <select id="editPriority" required>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="editStatus">Status:</label>
                    <select id="editStatus" required>
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="overdue">Tugas Terlewat</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-save">Save Changes</button>
                    <button type="button" class="btn-cancel" onclick="closeEditPopup()">Cancel</button>
            </div>
            </form>
        </div>
    </div>

    <div id="taskTooltip"></div>
    <div class="notification" id="notification"></div>

    <script>
        let currentDate = new Date();
        let tasks = [];
        let taskToEdit = null;

        // Initialize calendar and tasks
        document.addEventListener('DOMContentLoaded', function() {
            renderCalendar();
            loadTasks();
            
            // Event listeners for navigation
            document.getElementById('prevMonth').addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar();
            });
            
            document.getElementById('nextMonth').addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar();
            });

            // Task form submission
            document.getElementById('taskForm').addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Form submitted');
                saveTask();
            });

            // Filter buttons
            document.querySelectorAll('.filter-button').forEach(button => {
                button.addEventListener('click', function() {
                    const filter = this.dataset.filter;
                    filterTasks(filter);
                });
            });
        });

        function getMonthName(date) {
            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            return months[date.getMonth()];
        }

        function renderCalendar() {
            const grid = document.getElementById('calendarGrid');
            grid.innerHTML = '';
            
            // Update month display
            document.getElementById('currentMonth').textContent = 
                `${getMonthName(currentDate)} ${currentDate.getFullYear()}`;
            
            const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
            
            // Add day headers
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            days.forEach(day => {
                const dayHeader = document.createElement('div');
                dayHeader.className = 'calendar-day header';
                dayHeader.textContent = day;
                grid.appendChild(dayHeader);
            });
            
            // Add empty cells for days before first day of month
            for (let i = 0; i < firstDay.getDay(); i++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'calendar-day empty';
                grid.appendChild(emptyCell);
            }
            
            // Add days of the month
            for (let day = 1; day <= lastDay.getDate(); day++) {
                const dayCell = document.createElement('div');
                dayCell.className = 'calendar-day';
                
                const currentDateStr = `${currentDate.getFullYear()}-${(currentDate.getMonth() + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                const dayTasks = getTasksForDate(currentDateStr);
                
                const dayContent = document.createElement('div');
                dayContent.className = 'day-content';
                dayContent.innerHTML = `
                    <span class="day-number">${day}</span>
                    ${dayTasks.length > 0 ? `
                        <div class="day-tasks ${dayTasks.length > 3 ? 'has-more' : ''}">
                            ${dayTasks.slice(0, 3).map(task => `
                                <div class="calendar-task ${task.priority}" title="${task.title}">
                                    <span class="task-dot"></span>
                                    ${task.title}
                                </div>
                            `).join('')}
                    </div>
                    ` : ''}
                `;
                
                if (dayTasks.length > 0) {
                    dayCell.classList.add('has-tasks');
                }
                
                dayCell.appendChild(dayContent);
                dayCell.addEventListener('click', () => showTasksForDate(currentDateStr, dayTasks));
                
                grid.appendChild(dayCell);
            }
            
            // Add empty cells for days after last day of month if needed
            const lastDayOfWeek = lastDay.getDay();
            if (lastDayOfWeek < 6) {
                for (let i = lastDayOfWeek + 1; i < 7; i++) {
                    const emptyCell = document.createElement('div');
                    emptyCell.className = 'calendar-day empty';
                    grid.appendChild(emptyCell);
                }
            }
        }

        function loadTasks() {
            fetch('tasks_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=read',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    tasks = data.data;
                    renderTasks();
                    renderCalendar();
                    checkOverdueTasks();
                } else {
                    console.error('Error loading tasks:', data.message);
                    // Jangan tampilkan notifikasi error di sini
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Jangan tampilkan notifikasi error di sini
            });
        }

        function renderTasks() {
            console.log('Rendering tasks:', tasks); // Debug log
            const taskList = document.getElementById('taskList');
            taskList.innerHTML = '';
            
            if (!tasks || tasks.length === 0) {
                taskList.innerHTML = `
                    <div class="no-tasks-message">
                        <p>Tidak ada tugas yang tersedia</p>
                        <button onclick="openAddTaskModal()" class="add-task-btn">
                            <i class="fas fa-plus"></i> Tambah Tugas Baru
                        </button>
                    </div>
                `;
                return;
            }
            
            tasks.forEach(task => {
                console.log('Creating element for task:', task); // Debug log
                const taskElement = createTaskElement(task);
                taskList.appendChild(taskElement);
            });
        }

        function createTaskElement(task) {
            const div = document.createElement('div');
            div.className = 'task-list-item';
            
            // Format tanggal dengan pengecekan null
            const taskDate = task.due_date ? new Date(task.due_date).toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }) : 'Tanggal tidak tersedia';
            
            // Icon untuk priority
            const priorityIcons = {
                'low': '🔵',
                'medium': '🟡',
                'high': '🔴'
            };

            // Status badge dengan penanganan overdue
            let statusClass = 'status-' + (task.status || 'not_started');
            let statusText = '';
            
            switch(task.status) {
                case 'overdue':
                    statusText = 'Tugas Terlewat';
                    break;
                case 'completed':
                    statusText = 'Selesai';
                    break;
                case 'in_progress':
                    statusText = 'Sedang Dikerjakan';
                    break;
                case 'not_started':
                    statusText = 'Belum Dimulai';
                    break;
                default:
                    statusText = 'Belum Dimulai';
            }

            div.innerHTML = `
                <div class="task-header">
                    <h4>${task.title || 'Untitled'}</h4>
                    <span class="priority-badge ${task.priority || 'medium'}">
                        ${priorityIcons[task.priority] || '🟡'} ${(task.priority || 'medium').charAt(0).toUpperCase() + (task.priority || 'medium').slice(1)}
                    </span>
                                </div>
                <p class="task-description">${task.description || 'No description'}</p>
                <div class="task-meta">
                    <span class="task-date">📅 ${taskDate}</span>
                    <span class="${statusClass}">${statusText}</span>
                    ${task.completed_at ? `<span class="completed-date">✅ Selesai: ${new Date(task.completed_at).toLocaleDateString('id-ID')}</span>` : ''}
                    ${task.overdue_at ? `<span class="overdue-date">⚠️ Terlewat: ${new Date(task.overdue_at).toLocaleDateString('id-ID')}</span>` : ''}
                            </div>
                <div class="task-actions">
                    <button class="edit-btn" onclick="editTask(${task.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="delete-btn" onclick="deleteTask(${task.id})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    </div>
                `;
            
            // Tambahkan kelas khusus jika task overdue
            if (task.status === 'overdue') {
                div.classList.add('overdue-task');
            }
            
            return div;
        }

        function openAddTaskModal() {
            document.getElementById('modalTitle').textContent = 'Add New Task';
            document.getElementById('taskForm').reset();
            document.getElementById('taskId').value = '';
            document.getElementById('taskModal').style.display = 'block';
        }

        function resetForms() {
            document.getElementById('taskForm').reset();
            document.getElementById('editTaskForm').reset();
            document.getElementById('taskId').value = '';
            document.getElementById('editTaskId').value = '';
        }

        function closeTaskModal() {
            document.getElementById('taskModal').style.display = 'none';
            document.getElementById('taskForm').reset();
            console.log('Modal closed and form reset');
        }

        function saveTask() {
            // Ambil nilai dari form
            const title = document.getElementById('taskTitle').value;
            const description = document.getElementById('taskDescription').value;
            const dueDate = document.getElementById('taskDate').value;
            const priority = document.getElementById('taskPriority').value;
            const status = document.getElementById('taskStatus').value;
            
            // Validasi input
            if (!title || !description || !dueDate) {
                showNotification('Please fill all required fields', 'error');
                return;
            }
            
            // Buat objek task baru
            const newTask = {
                id: Date.now(), // Temporary ID
                title,
                description,
                due_date: dueDate,
                priority,
                status,
                user_id: <?php echo $_SESSION['user_id']; ?>
            };
            
            // Tambahkan task baru ke array tasks dan update tampilan
            tasks.push(newTask);
            renderTasks();
            renderCalendar();
            closeTaskModal();
            showNotification('Task berhasil ditambahkan', 'success');
            
            // Kirim ke server di background
            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('title', title);
            formData.append('description', description);
            formData.append('due_date', dueDate);
            formData.append('priority', priority);
            formData.append('status', status);
            formData.append('user_id', <?php echo $_SESSION['user_id']; ?>);

            fetch('tasks_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update ID task dengan ID dari server
                    const index = tasks.findIndex(t => t.id === newTask.id);
                    if (index !== -1) {
                        tasks[index].id = data.task_id;
                    }
                } else {
                    console.error('Error saving task:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function editTask(taskId) {
            console.log('Editing task:', taskId);
            
            // Cari task yang akan diedit
            const task = tasks.find(t => t.id == taskId);
            if (!task) {
                showNotification('Task not found', 'error');
                return;
            }
            
            // Debug log untuk melihat data task yang akan diedit
            console.log('Task data:', task);
            
            // Reset form terlebih dahulu
            document.getElementById('editTaskForm').reset();
            
            // Isi form dengan data task
            document.getElementById('editTaskId').value = task.id;
            document.getElementById('editTitle').value = task.title || '';
            document.getElementById('editDescription').value = task.description || '';
            document.getElementById('editDueDate').value = task.due_date || '';
            document.getElementById('editPriority').value = task.priority || 'medium';
            document.getElementById('editStatus').value = task.status || 'not_started';
            
            // Tampilkan popup
            const editPopup = document.getElementById('editTaskPopup');
            editPopup.style.display = 'block';
        }

        function confirmEdit() {
            if (taskToEdit) {
                document.getElementById('modalTitle').textContent = 'Edit Task';
                document.getElementById('taskId').value = taskToEdit.id;
                document.getElementById('taskTitle').value = taskToEdit.title || '';
                document.getElementById('taskDescription').value = taskToEdit.description || '';
                document.getElementById('taskDate').value = taskToEdit.due_date || '';
                document.getElementById('taskPriority').value = taskToEdit.priority || 'medium';
                document.getElementById('taskStatus').value = taskToEdit.status || 'not_started';
                
                document.getElementById('editConfirmModal').style.display = 'none';
                document.getElementById('taskModal').style.display = 'block';
            }
        }

        function cancelEdit() {
            document.getElementById('editConfirmModal').style.display = 'none';
            taskToEdit = null;
        }

        function deleteTask(taskId) {
            if (confirm('Apakah Anda yakin ingin menghapus tugas ini?')) {
                // Hapus task dari array lokal
                tasks = tasks.filter(t => t.id != taskId);
                renderTasks();
                renderCalendar();
                showNotification('Task berhasil dihapus', 'success');
                
                // Kirim delete request ke server di background
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', taskId);
                formData.append('user_id', <?php echo $_SESSION['user_id']; ?>);

                fetch('tasks_handler.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Error deleting task:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        function filterTasks(filter) {
            document.querySelectorAll('.filter-button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-filter="${filter}"]`).classList.add('active');
            
            const taskList = document.getElementById('taskList');
            taskList.innerHTML = '';
            
            let filteredTasks;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (filter === 'all') {
                filteredTasks = tasks;
            } else if (filter === 'overdue') {
                // Khusus untuk filter overdue, cek tanggal jatuh tempo
                filteredTasks = tasks.filter(task => {
                    if (task.status === 'completed') return false;
                    
                    const taskDate = new Date(task.due_date);
                    taskDate.setHours(0, 0, 0, 0);
                    return taskDate < today || task.status === 'overdue';
                });
            } else {
                filteredTasks = tasks.filter(task => task.status === filter);
            }
            
            if (filteredTasks.length === 0) {
                let message = '';
                switch (filter) {
                    case 'not_started':
                        message = 'Tidak ada tugas yang belum dimulai';
                        break;
                    case 'in_progress':
                        message = 'Tidak ada tugas yang sedang dikerjakan';
                        break;
                    case 'completed':
                        message = 'Tidak ada tugas yang selesai';
                        break;
                    case 'overdue':
                        message = 'Tidak ada tugas yang terlewat';
                        break;
                    default:
                        message = 'Tidak ada tugas yang tersedia';
                }
                
                taskList.innerHTML = `
                    <div class="no-tasks-message">
                        <p>${message}</p>
                        ${filter === 'all' ? `
                            <button onclick="openAddTaskModal()" class="add-task-btn">
                                <i class="fas fa-plus"></i> Tambah Tugas Baru
                            </button>
                        ` : ''}
                    </div>
                `;
                return;
            }
            
            // Urutkan tasks berdasarkan tanggal
            filteredTasks.sort((a, b) => new Date(a.due_date) - new Date(b.due_date));
            
            filteredTasks.forEach(task => {
                const taskElement = createTaskElement(task);
                taskList.appendChild(taskElement);
            });
        }

        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.classList.add('show');
            
            // Log untuk debugging
            console.log(`${type.toUpperCase()}: ${message}`);
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        function getTasksForDate(dateStr) {
            return tasks.filter(task => task.due_date === dateStr);
        }

        function showTasksForDate(dateStr, dayTasks) {
            const taskList = document.getElementById('taskList');
            taskList.innerHTML = '';
            
            // Update filter buttons
            document.querySelectorAll('.filter-button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector('[data-filter="all"]').classList.add('active');
            
            // Show tasks for selected date
            dayTasks.forEach(task => {
                const taskElement = createTaskElement(task);
                taskList.appendChild(taskElement);
            });
        }

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

                // Jika tanggal tugas sudah lewat dan status bukan completed
                if (taskDate < today) {
                    task.status = 'overdue'; // Update status lokal
                    
                    const formData = new FormData();
                    formData.append('action', 'update_status');
                    formData.append('id', task.id);
                    formData.append('status', 'overdue');
                    formData.append('user_id', task.user_id);

                    fetch('tasks_handler.php', {
                method: 'POST',
                        body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                            renderTasks(); // Update tampilan setelah mengubah status
                        }
                    })
                    .catch(error => console.error('Error updating overdue status:', error));
                }
            });
        }

        function closeEditPopup() {
            const editPopup = document.getElementById('editTaskPopup');
            editPopup.style.display = 'none';
            document.getElementById('editTaskForm').reset();
            console.log('Edit popup closed and form reset');
        }

        document.getElementById('editTaskForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const taskId = document.getElementById('editTaskId').value;
            const title = document.getElementById('editTitle').value;
            const description = document.getElementById('editDescription').value;
            const dueDate = document.getElementById('editDueDate').value;
            const priority = document.getElementById('editPriority').value;
            const status = document.getElementById('editStatus').value;
            
            if (!title || !description || !dueDate) {
                showNotification('Please fill all required fields', 'error');
                return;
            }
            
            // Update task di array lokal
            const taskIndex = tasks.findIndex(t => t.id == taskId);
            if (taskIndex !== -1) {
                tasks[taskIndex] = {
                    ...tasks[taskIndex],
                    title,
                    description,
                    due_date: dueDate,
                    priority,
                    status
                };
                
                // Update tampilan
                renderTasks();
                renderCalendar();
                closeEditPopup();
                showNotification('Task berhasil diperbarui', 'success');
            }
            
            // Kirim update ke server di background
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('id', taskId);
            formData.append('title', title);
            formData.append('description', description);
            formData.append('due_date', dueDate);
            formData.append('priority', priority);
            formData.append('status', status);
            formData.append('user_id', <?php echo $_SESSION['user_id']; ?>);
            
            fetch('tasks_handler.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Error updating task:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Tutup popup jika user mengklik di luar popup
        window.addEventListener('click', function(event) {
            const editPopup = document.getElementById('editTaskPopup');
            if (event.target === editPopup) {
                closeEditPopup();
            }
        });

        function toggleActionMenu(button) {
            // Tutup semua menu yang terbuka terlebih dahulu
            document.querySelectorAll('.action-menu.show').forEach(menu => {
                if (menu !== button.nextElementSibling) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle menu yang diklik
            const menu = button.nextElementSibling;
            menu.classList.toggle('show');
            
            // Log untuk debugging
            console.log('Menu toggled:', menu.classList.contains('show'));
        }

        // Tambahkan event listener untuk menutup menu saat mengklik di luar
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.task-actions')) {
                document.querySelectorAll('.action-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });

        // Fungsi pencarian
        function searchTasks(searchTerm) {
            searchTerm = searchTerm.toLowerCase().trim();
            
            // Jika search term kosong, tampilkan semua tugas
            if (!searchTerm) {
                renderTasks();
                return;
            }

            // Filter tugas berdasarkan kata kunci
            const filteredTasks = tasks.filter(task => {
                const title = (task.title || '').toLowerCase();
                const description = (task.description || '').toLowerCase();
                return title.includes(searchTerm) || description.includes(searchTerm);
            });

            // Tampilkan hasil pencarian
            const taskList = document.getElementById('taskList');
            taskList.innerHTML = '';

            if (filteredTasks.length === 0) {
                taskList.innerHTML = `
                    <div class="no-tasks-message">
                        <p>Tidak ada tugas yang cocok dengan pencarian: "${searchTerm}"</p>
                    </div>
                `;
                return;
            }

            filteredTasks.forEach(task => {
                const taskElement = createTaskElement(task);
                taskList.appendChild(taskElement);
            });
        }

        // Event listener untuk search bar
        document.addEventListener('DOMContentLoaded', function() {
            const searchBar = document.querySelector('.search-bar');
            const clearButton = document.querySelector('.search-clear');

            if (searchBar && clearButton) {
                // Event untuk input pencarian
                searchBar.addEventListener('input', function() {
                    const searchTerm = this.value.trim();
                    clearButton.style.display = searchTerm.length > 0 ? 'block' : 'none';
                    searchTasks(searchTerm);
                });

                // Event untuk tombol clear
                clearButton.addEventListener('click', function() {
                    searchBar.value = '';
                    clearButton.style.display = 'none';
                    renderTasks(); // Tampilkan kembali semua tugas
                    searchBar.focus();
                });

                // Event untuk tombol Enter
                searchBar.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchTasks(this.value);
                    }
                });
            }
        });
    </script>

    <script>
        // Add this at the bottom of your file, before </body>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('expanded');
            document.querySelector('.content').classList.toggle('shifted');
        });

        // Initialize theme
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || document.body.className || 'light';
            document.body.className = savedTheme;
            document.querySelector('.navbar').className = `navbar ${savedTheme}`;
            document.querySelector('.sidebar').className = `sidebar ${savedTheme}`;
        });
    </script>

    <style>
        .action-toggle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background: var(--button-bg);
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-toggle::after {
            content: "⋮";
            font-size: 20px;
            color: var(--text-color);
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
        }

        .action-item {
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
            background: transparent;
            color: var(--text-color);
        }

        .action-item:hover {
            background-color: var(--button-hover);
        }

        .action-item.edit {
            color: #3b82f6;
        }

        .action-item.delete {
            color: #ef4444;
        }

        .task-actions {
            position: relative;
            margin-top: 10px;
        }
    </style>
</body>
</html>