// View Switching
function switchView(viewId, element) {
    // Hide all views
    document.querySelectorAll('.view-section').forEach(el => el.style.display = 'none');
    
    // Deactivate all nav links
    document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
    
    const target = document.getElementById(`view-${viewId}`);
    if (target) {
        target.style.display = 'block';
    }
    
    if (element) {
        element.classList.add('active');
    } else {
        // If no element passed, try to find the matching nav link
        document.querySelectorAll('.nav-link').forEach(link => {
            if (link.getAttribute('onclick')?.includes(`'${viewId}'`)) {
                link.classList.add('active');
            }
        });
    }
}

// Search Filtering
function filterList(query, listId) {
    const list = document.getElementById(listId);
    const items = list.querySelectorAll('.list-item');
    const term = query.toLowerCase();

    items.forEach(item => {
        const title = item.querySelector('.item-title').innerText.toLowerCase();
        const desc = item.querySelector('.item-desc')?.innerText.toLowerCase() || '';
        if (title.includes(term) || desc.includes(term)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Task Status Filtering (Subnav)
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const filter = this.innerText.trim();
        const list = document.getElementById('tasks-list');
        const items = list.querySelectorAll('.list-item');

        // Toggle active state visually if needed, for now just show/hide
        items.forEach(item => {
            const status = item.getAttribute('data-status');
            if (filter === 'À Faire' && status === 'pending') item.style.display = 'flex';
            else if (filter === 'Terminés' && status === 'completed') item.style.display = 'flex';
            else if (filter === 'Tickets actifs' || filter === 'Tout') item.style.display = 'flex';
            else if (filter === 'En progression' && status === 'pending') item.style.display = 'flex';
            else item.style.display = 'none';
        });

        // Ensure we are in tasks view
        switchView('tasks');
    });
});

// Topbar Icons Feedback
document.querySelectorAll('.icon-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const icon = this.querySelector('i');
        if (icon.classList.contains('fa-bell')) showToast('Pas de nouvelles notifications.');
        if (icon.classList.contains('fa-question-circle')) showToast('Centre d\'aide ouvert dans un nouvel onglet.');
        if (icon.classList.contains('fa-cog')) showToast('Paramètres utilisateur bientôt disponibles.');
    });
});

// Modals
function openModal(id) {
    document.getElementById(id).classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

// Exceptional Task toggle fields
document.getElementById('is_exceptional_cb')?.addEventListener('change', function() {
    const fields = document.getElementById('reminder-fields');
    if (this.checked) {
        fields.style.display = 'flex';
        fields.querySelectorAll('input').forEach(inp => inp.required = true);
    } else {
        fields.style.display = 'none';
        fields.querySelectorAll('input').forEach(inp => {
            inp.required = false;
            inp.value = '';
        });
    }
});

// Toast Notifications
function showToast(message, isError = false) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = 'toast';
    if(isError) {
        toast.style.backgroundColor = 'var(--danger)';
    }
    toast.innerHTML = `
        <i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i>
        <span>${message}</span>
    `;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Checkboxes Visual toggle in list
document.addEventListener('click', function(e) {
    const listItem = e.target.closest('.list-item');
    if (listItem) {
        if(e.target.closest('.item-actions') || e.target.closest('.custom-checkbox')) return;
        
        const parent = listItem.closest('.item-list');
        parent.querySelectorAll('.list-item').forEach(i => i.classList.remove('selected'));
        listItem.classList.add('selected');
    }
});

// AJAX Form Submit for Task
document.getElementById('task-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'add');
    
    // Format reminder date and time into one SQL datetime
    const date = formData.get('reminder_d');
    const time = formData.get('reminder_t');
    if (date && time) {
        formData.append('reminder_date', `${date} ${time}:00`);
    }

    try {
        const res = await fetch('api/tasks.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showToast('Tâche ajoutée avec succès !');
            if (this.querySelector('input[name="create_another"]')?.checked) {
                this.reset();
                document.getElementById('reminder-fields').style.display = 'none';
            } else {
                closeModal('task-modal');
                setTimeout(() => location.reload(), 1000);
            }
        } else {
            showToast('Erreur: ' + (data.error || 'Inconnue'), true);
        }
    } catch(err) {
        showToast('Erreur réseau', true);
    }
});

// AJAX Form Submit for Note
document.getElementById('note-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'add');
    
    try {
        const res = await fetch('api/notes.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showToast('Note ajoutée avec succès !');
            closeModal('note-modal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Erreur serveur', true);
        }
    } catch(err) {
        showToast('Erreur réseau', true);
    }
});

// Dynamic toggling and deletion
async function toggleTask(id) {
    const res = await fetch('api/tasks.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'toggle', id: id })
    });
    const data = await res.json();
    if(data.success) {
        const item = document.getElementById(`task-${id}`);
        const title = item.querySelector('.item-title');
        
        if(item.getAttribute('data-status') === 'completed') {
            item.setAttribute('data-status', 'pending');
            title.style.textDecoration = 'none';
            title.style.color = 'var(--text-primary)';
        } else {
            item.setAttribute('data-status', 'completed');
            title.style.textDecoration = 'line-through';
            title.style.color = 'var(--text-secondary)';
        }
        showToast('Statut mis à jour');
    }
}

async function deleteTask(id) {
    if(!confirm("Êtes-vous sûr de vouloir supprimer cette tâche ?")) return;
    const res = await fetch('api/tasks.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'delete', id: id })
    });
    const data = await res.json();
    if(data.success) {
        document.getElementById(`task-${id}`).remove();
        showToast("Tâche supprimée");
    }
}

async function toggleFavorite(type, id) {
    const apiPath = type === 'task' ? 'api/tasks.php' : 'api/notes.php';
    const res = await fetch(apiPath, {
        method: 'POST',
        body: new URLSearchParams({ action: 'toggle_favorite', id: id })
    });
    const data = await res.json();
    if(data.success) {
        showToast('Favoris mis à jour');
        setTimeout(() => location.reload(), 500);
    }
}

async function deleteNote(id) {
    if(!confirm("Êtes-vous sûr de vouloir supprimer cette note ?")) return;
    const res = await fetch('api/notes.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'delete', id: id })
    });
    const data = await res.json();
    if(data.success) {
        document.getElementById(`note-${id}`).remove();
        showToast("Note supprimée");
    }
}

// AJAX Form Submit for Profile
document.getElementById('profile-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const res = await fetch('api/settings.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            showToast('Profil mis à jour !');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Erreur: ' + (data.error || 'Inconnue'), true);
        }
    } catch(err) {
        showToast('Erreur réseau', true);
    }
});

// Generic Handler for API Forms (Password, etc)
document.querySelectorAll('.api-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const apiPath = this.dataset.api || 'api/settings.php';
        
        try {
            const res = await fetch(apiPath, { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                showToast('Paramètres enregistrés !');
                if (this.id === 'password-form') this.reset();
            } else {
                showToast(data.error || 'Erreur', true);
            }
        } catch (err) {
            showToast('Erreur réseau', true);
        }
    });
});

// Initialize Theme on Load (Cleaned up)
(function initTheme() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
    }
})();

function filterByStatus(status, btn) {
    // UI: Update active state
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const tasks = document.querySelectorAll('#tasks-list .list-item');
    tasks.forEach(task => {
        if (status === 'all') {
            task.style.display = 'flex';
        } else if (status === 'exceptional') {
            task.style.display = task.classList.contains('exceptional') ? 'flex' : 'none';
        } else {
            task.style.display = task.getAttribute('data-status') === status ? 'flex' : 'none';
        }
    });

    // Automatically switch to task view if not already there
    if (document.getElementById('view-tasks').style.display === 'none') {
        switchView('tasks', document.querySelector('.nav-link[onclick*="tasks"]'));
    }
}

// Reminders Check Interval
function checkReminders() {
    const now = Date.now();
    document.querySelectorAll('.reminder-time').forEach(el => {
        const time = parseInt(el.getAttribute('data-time'), 10);
        const card = el.closest('.list-item');
        if (time > now && (time - now) < 60000 && !card.dataset.notified) {
            const title = card.querySelector('.item-title').innerText;
            alert(`⏰ RAPPEL DE TÂCHE :\n${title}`);
            card.dataset.notified = "true";
        }
    });
}
setInterval(checkReminders, 30000); 
setTimeout(checkReminders, 2000);

// Toggle Theme (Dark/Light)
function toggleTheme() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    showToast(isDark ? 'Mode sombre activé' : 'Mode clair activé');
}

