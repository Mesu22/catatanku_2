<?php
require_once 'config/database.php';

// Get tasks from database
$tasks_query = "SELECT * FROM tasks WHERE user_id = 1 ORDER BY due_date, due_time"; 
$tasks_result = $pdo->query($tasks_query);

$tasks = array();
if ($tasks_result) {
    while ($row = $tasks_result->fetch(PDO::FETCH_ASSOC)) {
        $date = $row['due_date'];
        if (!isset($tasks[$date])) {
            $tasks[$date] = array();
        }
        $tasks[$date][] = array(
            'id' => $row['id'],
            'title' => $row['title'], 
            'description' => $row['description'],
            'status' => $row['status'],
            'time' => $row['due_time']
        );
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender - CatatanKu</title>
    <link rel="stylesheet" href="public/css/kalender/kalender.css">
    <link rel="stylesheet" href="public/css/sidebar/sidebar.css">
    <link rel="stylesheet" href="public/css/beranda/modal/modal.css">
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <img src="img/logo/logo mesa.png" alt="CatatanKu Logo">
        <input type="text" class="search-bar" placeholder="Cari tugas anda...">
    </div>

    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="calendar-section">
            <div class="calendar-slider">
                <!-- Calendar containers will be dynamically loaded here -->
            </div>
            <div class="calendar-nav">
                <button class="nav-btn" onclick="prevMonth()">&#8592; Sebelumnya</button>
                <button class="nav-btn" onclick="nextMonth()">Selanjutnya &#8594;</button>
            </div>
        </div>

        <div class="task-list-section">
            <h2 class="task-list-header">Daftar Tugas</h2>
            <div id="taskList">
                <!-- Task list will be dynamically loaded here -->
            </div>
        </div>
    </div>

    <!-- Task Modal -->
    <div id="taskModal" class="task-modal">
        <div class="task-modal-content">
            <span class="close-modal">&times;</span>
            <h2>Tambah Tugas Baru</h2>
            <form class="task-form" onsubmit="addTask(event)">
                <div class="form-group">
                    <label for="taskTitle">Judul Tugas</label>
                    <input type="text" id="taskTitle" placeholder="Masukkan judul tugas" required>
                </div>
                
                <div class="form-group">
                    <label for="taskDescription">Deskripsi Tugas</label>
                    <textarea id="taskDescription" placeholder="Masukkan deskripsi tugas" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="taskStatus">Status</label>
                    <select id="taskStatus">
                        <option value="not_started">Belum Dimulai</option>
                        <option value="in_progress">Sedang Berjalan</option>
                        <option value="completed">Selesai</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="taskTime">Waktu</label>
                    <input type="time" id="taskTime" required>
                </div>
                
                <input type="hidden" id="taskDate">
                <button type="submit">Tambah Tugas</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="edit-modal">
        <div class="edit-modal-content">
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
            <h2>Edit Tugas</h2>
            <form class="edit-form" onsubmit="updateTask(event)">
                <input type="text" id="editTaskTitle" placeholder="Judul Tugas" required>
                <textarea id="editTaskDescription" placeholder="Deskripsi Tugas" rows="4"></textarea>
                <select id="editTaskStatus">
                    <option value="not_started">Belum Dimulai</option>
                    <option value="in_progress">Sedang Berjalan</option>
                    <option value="completed">Selesai</option>
                </select>
                <input type="time" id="editTaskTime" required>
                <input type="hidden" id="editTaskDate">
                <input type="hidden" id="editTaskId">
                <button type="submit">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="detail-modal">
        <div class="detail-modal-content">
            <div class="detail-header">
                <h2>Detail Tugas</h2>
                <span class="close-modal" onclick="closeDetailModal()">&times;</span>
            </div>
            <div class="detail-grid">
                <div class="detail-field">
                    <label>Judul</label>
                    <p id="detailTitle"></p>
                </div>
                <div class="detail-field">
                    <label>Status</label>
                    <p><span id="detailStatus" class="status-badge"></span></p>
                </div>
                <div class="detail-field">
                    <label>Tanggal</label>
                    <p id="detailDate"></p>
                </div>
                <div class="detail-field">
                    <label>Waktu</label>
                    <p id="detailTime"></p>
                </div>
                <div class="detail-field detail-description">
                    <label>Deskripsi</label>
                    <p id="detailDescription"></p>
                </div>
            </div>
            <div class="detail-actions">
                <button class="btn-close" onclick="closeDetailModal()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="delete-confirm-modal">
        <div class="delete-confirm-content">
            <h2>Konfirmasi Hapus</h2>
            <p>Apakah Anda yakin ingin menghapus tugas ini?</p>
            <div class="delete-confirm-buttons">
                <button class="confirm-delete-btn" onclick="confirmDelete()">Ya</button>
                <button class="cancel-delete-btn" onclick="closeDeleteModal()">Tidak</button>
            </div>
        </div>
    </div>

    <script>
        const months = [
            "Januari", "Februari", "Maret", "April", 
            "Mei", "Juni", "Juli", "Agustus",
            "September", "Oktober", "November", "Desember"
        ];
        
        let currentDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();
        let tasks = <?php echo json_encode($tasks); ?>;
        let taskToDelete = null;
        let currentDetailTask = null;

        function getCurrentDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = (today.getMonth() + 1).toString().padStart(2, '0');
            const day = today.getDate().toString().padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function generateCalendar(month, year) {
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDay = firstDay.getDay();

            let calendarHTML = `
                <div class="calendar-container">
                    <div class="calendar-header">
                        <h2>${months[month]} ${year}</h2>
                    </div>
                    <div class="calendar-grid">
                        <div class="calendar-day"><div class="day-header">Min</div></div>
                        <div class="calendar-day"><div class="day-header">Sen</div></div>
                        <div class="calendar-day"><div class="day-header">Sel</div></div>
                        <div class="calendar-day"><div class="day-header">Rab</div></div>
                        <div class="calendar-day"><div class="day-header">Kam</div></div>
                        <div class="calendar-day"><div class="day-header">Jum</div></div>
                        <div class="calendar-day"><div class="day-header">Sab</div></div>
            `;

            // Previous month's days
            const prevMonth = new Date(year, month, 0);
            const prevMonthDays = prevMonth.getDate();
            for (let i = startingDay - 1; i >= 0; i--) {
                calendarHTML += `
                    <div class="calendar-day other-month">
                        <div class="day-header">${prevMonthDays - i}</div>
                    </div>
                `;
            }

            // Current month's days
            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${year}-${(month + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                const isToday = day === currentDate.getDate() && 
                               month === currentDate.getMonth() && 
                               year === currentDate.getFullYear();
                
                let taskList = '';
                if (tasks[dateStr]) {
                    taskList = tasks[dateStr].map(task => 
                        `<div class="task-item" onclick="openDetailModal('${dateStr}', ${task.id}); event.stopPropagation();">${task.title}</div>`
                    ).join('');
                }

                calendarHTML += `
                    <div class="calendar-day ${isToday ? 'today' : ''}" onclick="openTaskModal('${dateStr}')">
                        <div class="day-header">${day}</div>
                        ${taskList}
                    </div>
                `;
            }

            // Next month's days
            const totalCells = 42;
            const remainingCells = totalCells - (startingDay + daysInMonth);
            for (let i = 1; i <= remainingCells; i++) {
                calendarHTML += `
                    <div class="calendar-day other-month">
                        <div class="day-header">${i}</div>
                    </div>
                `;
            }

            calendarHTML += `
                    </div>
                </div>
            `;

            return calendarHTML;
        }

        function updateCalendar() {
            const slider = document.querySelector('.calendar-slider');
            slider.innerHTML = generateCalendar(currentMonth, currentYear);
            updateTaskList();
        }

        function updateTaskList() {
            const taskList = document.getElementById('taskList');
            taskList.innerHTML = '';
            
            let allTasks = [];
            for (let date in tasks) {
                tasks[date].forEach(task => {
                    allTasks.push({...task, date});
                });
            }
            
            allTasks.sort((a, b) => {
                if (a.date !== b.date) return a.date.localeCompare(b.date);
                return a.time.localeCompare(b.time);
            });
            
            allTasks.forEach(task => {
                const taskDate = new Date(task.date);
                const formattedDate = `${taskDate.getDate()} ${months[taskDate.getMonth()]} ${taskDate.getFullYear()}`;
                
                taskList.innerHTML += `
                    <div class="task-list-item" onclick="openDetailModal('${task.date}', ${task.id})">
                        <div class="task-actions">
                            <div class="dropdown">
                                <button class="dropdown-toggle" onclick="toggleDropdown(this)">⋮</button>
                                <div class="dropdown-menu">
                                    <button onclick="openEditModal('${task.date}', ${task.id})">Edit</button>
                                    <button onclick="openDeleteModal('${task.date}', ${task.id})">Hapus</button>
                                </div>
                            </div>
                        </div>
                        <h3>${task.title}</h3>
                        <p>${task.description || 'Tidak ada deskripsi'}</p>
                        <div class="time">${formattedDate} - ${task.time}</div>
                        <div class="status">Status: ${task.status}</div>
                    </div>
                `;
            });
        }

        function openDetailModal(date, id) {
            const task = tasks[date].find(t => t.id === id);
            currentDetailTask = { date, id };
            
            const taskDate = new Date(date);
            const formattedDate = `${taskDate.getDate()} ${months[taskDate.getMonth()]} ${taskDate.getFullYear()}`;
            
            document.getElementById('detailTitle').textContent = task.title;
            document.getElementById('detailDescription').textContent = task.description || 'Tidak ada deskripsi';
            document.getElementById('detailDate').textContent = formattedDate;
            document.getElementById('detailTime').textContent = task.time;
            
            const statusElement = document.getElementById('detailStatus');
            statusElement.className = `status-badge status-${task.status}`;
            statusElement.textContent = getStatusText(task.status);
            
            document.getElementById('detailModal').style.display = 'block';
        }

        function getStatusText(status) {
            switch(status) {
                case 'not_started': return 'Belum Dimulai';
                case 'in_progress': return 'Sedang Berjalan';
                case 'completed': return 'Selesai';
                default: return status;
            }
        }

        function closeDetailModal() {
            document.getElementById('detailModal').style.display = 'none';
            currentDetailTask = null;
        }

        function openEditModalFromDetail() {
            if (currentDetailTask) {
                closeDetailModal();
                openEditModal(currentDetailTask.date, currentDetailTask.id);
            }
        }

        function openDeleteModalFromDetail() {
            if (currentDetailTask) {
                closeDetailModal();
                openDeleteModal(currentDetailTask.date, currentDetailTask.id);
            }
        }

        function toggleDropdown(button) {
            // Close all other dropdowns first
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            dropdowns.forEach(dropdown => {
                if (dropdown !== button.nextElementSibling) {
                    dropdown.classList.remove('show');
                }
            });
            
            // Toggle the clicked dropdown
            button.nextElementSibling.classList.toggle('show');
        }

        // Close dropdowns when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.dropdown-toggle')) {
                const dropdowns = document.querySelectorAll('.dropdown-menu');
                dropdowns.forEach(dropdown => {
                    if (dropdown.classList.contains('show')) {
                        dropdown.classList.remove('show');
                    }
                });
            }
        }

        function prevMonth() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            updateCalendar();
        }

        function nextMonth() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            updateCalendar();
        }

        // Task Modal Functions
        const modal = document.getElementById('taskModal');
        const editModal = document.getElementById('editModal');
        const deleteModal = document.getElementById('deleteConfirmModal');
        const closeBtn = document.querySelector('.close-modal');

        function openTaskModal(date) {
            document.getElementById('taskDate').value = date;
            modal.style.display = "block";
        }

        function openEditModal(date, id) {
            const task = tasks[date].find(t => t.id === id);
            document.getElementById('editTaskTitle').value = task.title;
            document.getElementById('editTaskDescription').value = task.description || '';
            document.getElementById('editTaskStatus').value = task.status;
            document.getElementById('editTaskTime').value = task.time;
            document.getElementById('editTaskDate').value = date;
            document.getElementById('editTaskId').value = id;
            editModal.style.display = "block";
        }

        function openDeleteModal(date, id) {
            taskToDelete = { date, id };
            deleteModal.style.display = "block";
        }

        function closeEditModal() {
            editModal.style.display = "none";
        }

        function closeDeleteModal() {
            deleteModal.style.display = "none";
            taskToDelete = null;
        }

        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
            if (event.target == editModal) {
                editModal.style.display = "none";
            }
            if (event.target == deleteModal) {
                closeDeleteModal();
            }
            if (event.target == document.getElementById('detailModal')) {
                closeDetailModal();
            }
        }

        function addTask(event) {
            event.preventDefault();
            
            const date = document.getElementById('taskDate').value;
            const title = document.getElementById('taskTitle').value;
            const description = document.getElementById('taskDescription').value;
            const status = document.getElementById('taskStatus').value;
            const time = document.getElementById('taskTime').value;

            // Send AJAX request to add task
            fetch('add_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    date: date,
                    title: title,
                    description: description,
                    status: status,
                    time: time
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (!tasks[date]) {
                        tasks[date] = [];
                    }
                    tasks[date].push({
                        id: data.task_id,
                        title,
                        description,
                        status,
                        time
                    });
                    modal.style.display = "none";
                    event.target.reset();
                    updateCalendar();
                }
            });
        }

        function updateTask(event) {
            event.preventDefault();
            
            const date = document.getElementById('editTaskDate').value;
            const id = parseInt(document.getElementById('editTaskId').value);
            const title = document.getElementById('editTaskTitle').value;
            const description = document.getElementById('editTaskDescription').value;
            const status = document.getElementById('editTaskStatus').value;
            const time = document.getElementById('editTaskTime').value;

            // Send AJAX request to update task
            fetch('update_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    date: date,
                    title: title,
                    description: description,
                    status: status,
                    time: time
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const taskIndex = tasks[date].findIndex(t => t.id === id);
                    tasks[date][taskIndex] = {
                        id,
                        title,
                        description,
                        status,
                        time
                    };
                    editModal.style.display = "none";
                    updateCalendar();
                }
            });
        }

        function confirmDelete() {
            if (taskToDelete) {
                const { date, id } = taskToDelete;
                
                // Send AJAX request to delete task
                fetch('delete_task.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const taskIndex = tasks[date].findIndex(t => t.id === id);
                        tasks[date].splice(taskIndex, 1);
                        if (tasks[date].length === 0) {
                            delete tasks[date];
                        }
                        closeDeleteModal();
                        updateCalendar();
                    }
                });
            }
        }

        // Initial calendar load
        updateCalendar();

        // Add sidebar toggle functionality
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('expanded');
            document.querySelector('.content').classList.toggle('shifted');
        });
    </script>
</body>
</html>
