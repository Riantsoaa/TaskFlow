<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Updated session in case it changed
$_SESSION['user_nom'] = $user['nom'];
$user_status = $user['status'] ?? 'Disponible';
$user_bio = $user['bio'] ?? '';

// Get tasks
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

$completedTasks = count(array_filter($tasks, function($t) { return $t['status'] === 'completed'; }));
$totalTasks = count($tasks);
$taskProgress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

// Get notes
$stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Load flatpickr for better date/time inputs if needed, though native goes well. -->
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <a href="#" class="sidebar-logo">
                <i class="fas fa-layer-group"></i>
                <h2>TaskFlow</h2>
            </a>

            <div class="nav-section">
                <ul class="nav-links">
                    <li class="nav-item">
                        <a href="#" class="nav-link active" onclick="switchView('overview', this)">
                            <i class="fas fa-th-large"></i> Espace de travail
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="switchView('tasks', this)">
                            <i class="fas fa-tasks"></i> Tâches
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link active" onclick="switchView('notes', this)">
                            <i class="far fa-file-alt"></i> Notes
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section" style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <ul class="nav-links">
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="switchView('recent', this)">
                            <i class="far fa-clock"></i> Récents <i class="fas fa-chevron-right" style="margin-left:auto;font-size:0.7rem;"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="switchView('favorites', this)">
                            <i class="far fa-star"></i> Favoris <i class="fas fa-chevron-right" style="margin-left:auto;font-size:0.7rem;"></i>
                        </a>
                    </li>
                    <li class="nav-item" style="margin-top: 2rem;">
                        <a href="logout.php" class="nav-link" style="color: var(--danger);">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>

            <div class="progress-card">
                <div class="progress-title">Progression des tâches</div>
                
                <div class="progress-item">
                    <div class="progress-header">
                        <span>Tâches complétées</span>
                        <span><?php echo $completedTasks; ?>/<?php echo $totalTasks; ?></span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill bg-blue" style="width: <?php echo $taskProgress; ?>%;"></div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="main-wrapper">
            <!-- Topbar -->
            <header class="topbar">
                <div class="topbar-left">
                    <div class="workspace-selector">
                        <i class="fas fa-th-large" style="color:var(--text-secondary);"></i>
                        Groupe - Personnel
                        <i class="fas fa-caret-down"></i>
                    </div>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher">
                    </div>
                </div>
                <div class="topbar-right">
                    <div style="text-align: right; margin-right: 1rem;">
                        <div style="font-weight: 600; font-size: 0.8125rem;"><?php echo htmlspecialchars($_SESSION['user_nom']); ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-secondary); font-style: italic; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                             "<?php echo htmlspecialchars($user_status); ?>"
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="openModal('task-modal')">Créer +</button>
                    <button class="icon-btn"><i class="far fa-bell"></i></button>
                    <button class="icon-btn"><i class="far fa-question-circle"></i></button>
                    <button class="icon-btn" onclick="openModal('settings-modal')"><i class="fas fa-cog"></i></button>
                    <img src="<?php echo (!empty($user['pdp']) && $user['pdp'] !== 'default.png') ? 'uploads/'.$user['pdp'] : 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['user_nom']).'&background=random'; ?>" alt="Avatar" class="avatar" style="cursor: pointer;" onclick="openModal('profile-modal')">
                </div>
            </header>

            <!-- Sub Nav -->
            <div class="subnav">
                <span class="subnav-label" style="cursor: pointer;" onclick="switchView('overview', document.querySelector('.nav-link[onclick*=\'overview\']'))">Espace de travail</span>
                <div class="avatars-group">
                    <img src="https://ui-avatars.com/api/?name=1&background=random" alt="">
                    <img src="https://ui-avatars.com/api/?name=2&background=random" alt="">
                    <img src="https://ui-avatars.com/api/?name=3&background=random" alt="">
                </div>
                
                <div style="width: 1px; height: 20px; background: var(--border-color); margin: 0 1rem;"></div>
                
                <button class="filter-btn active" onclick="filterByStatus('all', this)"><i class="far fa-check-circle"></i> Tout</button>
                <button class="filter-btn" onclick="filterByStatus('pending', this)"><i class="far fa-clock"></i> En progression</button>
                <button class="filter-btn" onclick="filterByStatus('completed', this)"><i class="fas fa-check"></i> Terminés</button>
                <button class="filter-btn" onclick="filterByStatus('exceptional', this)"><i class="fas fa-exclamation-triangle"></i> Urgent</button>
            </div>

            <!-- Views -->
            <div class="content-area">
                
                <!-- OVERVIEW VIEW -->
                <div id="view-overview" class="view-section" style="display: block;">
                    <div class="content-header">
                        <h2 class="content-title">Tableau général</h2>
                    </div>
                    <div class="grid-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                        <div class="progress-card" style="margin-top: 0;">
                            <div class="progress-title">Résumé des tâches</div>
                            <div style="font-size: 2rem; font-weight: 700; margin: 1rem 0;"><?php echo $completedTasks; ?> / <?php echo $totalTasks; ?></div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem;">Tâches terminées</p>
                        </div>
                        <div class="progress-card" style="margin-top: 0;">
                            <div class="progress-title">Notes récentes</div>
                            <div style="font-size: 2rem; font-weight: 700; margin: 1rem 0;"><?php echo count($notes); ?></div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem;">Notes enregistrées</p>
                        </div>
                    </div>
                </div>

                <!-- NOTES VIEW -->
                <div id="view-notes" class="view-section" style="display: none;">
                    <div class="content-header">
                        <h2 class="content-title">Mes Notes</h2>
                        <button class="btn btn-primary" onclick="openModal('note-modal')">Nouvelle Note +</button>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Rechercher des notes..." onkeyup="filterList(this.value, 'notes-list')">
                        </div>
                    </div>

                    <div class="tabs">
                        <div class="tab active">Toutes</div>
                        <div class="tab">Date <i class="fas fa-chevron-down" style="font-size:0.7rem;margin-left:0.25rem;"></i></div>
                    </div>

                    <div class="item-list" id="notes-list">
                        <?php if (empty($notes)): ?>
                            <div class="empty-state">
                                <i class="far fa-folder-open" style="font-size: 5rem; color: #dcdfe4; margin-bottom: 1rem;"></i>
                                <h3 style="color: var(--text-secondary);">Aucune note</h3>
                            </div>
                        <?php else: foreach ($notes as $index => $note): ?>
                            <div class="list-item" id="note-<?php echo $note['id']; ?>">
                                <input type="checkbox" class="custom-checkbox">
                                <div class="item-content">
                                    <div class="item-title"><?php echo htmlspecialchars($note['title']); ?></div>
                                    <div class="item-desc"><?php echo nl2br(htmlspecialchars($note['content'])); ?></div>
                                    <div class="item-meta">
                                        <i class="far fa-calendar-alt"></i> <?php echo date('d M Y à H:i', strtotime($note['created_at'])); ?>
                                        <?php if (!empty($note['attachment'])): ?>
                                            <a href="uploads/<?php echo $note['attachment']; ?>" target="_blank" style="margin-left: 1rem; color: var(--primary-color); text-decoration: none;">
                                                <i class="fas fa-paperclip"></i> Voir la pièce jointe
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <button class="action-btn" title="Favori" onclick="toggleFavorite('note', <?php echo $note['id']; ?>)">
                                        <i class="<?php echo isset($note['is_favorite']) && $note['is_favorite'] ? 'fas' : 'far'; ?> fa-star" style="color: #f59e0b;"></i>
                                    </button>
                                    <button class="action-btn" title="Supprimer" onclick="deleteNote(<?php echo $note['id']; ?>)"><i class="far fa-trash-alt"></i></button>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- TASKS VIEW -->
                <div id="view-tasks" class="view-section" style="display: none;">
                    <div class="content-header">
                        <h2 class="content-title">Mes Tâches</h2>
                        <button class="btn btn-primary" onclick="openModal('task-modal')">Nouvelle Tâche +</button>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Rechercher des tâches..." onkeyup="filterList(this.value, 'tasks-list')">
                        </div>
                    </div>

                    <div class="item-list" id="tasks-list">
                        <?php if (empty($tasks)): ?>
                            <div class="empty-state">
                                <i class="far fa-check-circle" style="font-size: 5rem; color: #dcdfe4; margin-bottom: 1rem;"></i>
                                <h3 style="color: var(--text-secondary);">Aucune tâche</h3>
                            </div>
                        <?php else: foreach ($tasks as $task): ?>
                            <div class="list-item <?php echo $task['is_exceptional'] ? 'exceptional' : ''; ?>" id="task-<?php echo $task['id']; ?>" data-status="<?php echo $task['status']; ?>">
                                <input type="checkbox" class="custom-checkbox" <?php echo $task['status'] === 'completed' ? 'checked' : ''; ?> onchange="toggleTask(<?php echo $task['id']; ?>)">
                                <div class="item-content">
                                    <div class="item-title" style="<?php echo $task['status'] === 'completed' ? 'text-decoration: line-through; color: var(--text-secondary);' : ''; ?>">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </div>
                                    <?php if ($task['description']): ?>
                                        <div class="item-desc"><?php echo htmlspecialchars($task['description']); ?></div>
                                    <?php endif; ?>
                                    <div class="item-meta">
                                        <i class="far fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($task['created_at'])); ?>
                                        <?php if ($task['is_exceptional'] && $task['reminder_date']): ?>
                                            <span style="color: var(--warning); margin-left: 1rem; font-weight: 600;">
                                                <i class="far fa-clock"></i> Rappel : <span class="reminder-time" data-time="<?php echo strtotime($task['reminder_date']) * 1000; ?>"><?php echo date('d/m/Y H:i', strtotime($task['reminder_date'])); ?></span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($task['attachment'])): ?>
                                            <a href="uploads/<?php echo $task['attachment']; ?>" target="_blank" style="margin-left: 1rem; color: var(--primary-color); text-decoration: none;">
                                                <i class="fas fa-paperclip"></i> Pièce jointe
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <button class="action-btn" title="Favori" onclick="toggleFavorite('task', <?php echo $task['id']; ?>)">
                                        <i class="<?php echo isset($task['is_favorite']) && $task['is_favorite'] ? 'fas' : 'far'; ?> fa-star" style="color: #f59e0b;"></i>
                                    </button>
                                    <button class="action-btn" title="Supprimer" onclick="deleteTask(<?php echo $task['id']; ?>)"><i class="far fa-trash-alt"></i></button>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- RECENT VIEW -->
                <div id="view-recent" class="view-section" style="display: none;">
                    <div class="content-header">
                        <h2 class="content-title">Activités Récentes</h2>
                    </div>
                    <div class="item-list">
                        <?php 
                        $allRecent = array_merge(
                            array_map(function($t){ $t['type']='task'; return $t; }, array_slice($tasks, 0, 5)),
                            array_map(function($n){ $n['type']='note'; return $n; }, array_slice($notes, 0, 5))
                        );
                        usort($allRecent, function($a, $b){ return strtotime($b['created_at']) - strtotime($a['created_at']); });
                        ?>
                        <?php if (empty($allRecent)): ?>
                            <p style="text-align:center; color:var(--text-secondary);">Aucune activité récente.</p>
                        <?php else: foreach($allRecent as $item): ?>
                            <div class="list-item">
                                <i class="<?php echo $item['type']==='task' ? 'fas fa-tasks' : 'far fa-file-alt'; ?>" style="color: var(--primary-color); margin-top: 5px;"></i>
                                <div class="item-content">
                                    <div class="item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                    <div class="item-meta">Modifié le <?php echo date('d M Y à H:i', strtotime($item['created_at'])); ?></div>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- FAVORITES VIEW -->
                <div id="view-favorites" class="view-section" style="display: none;">
                    <div class="content-header">
                        <h2 class="content-title">Favoris</h2>
                    </div>
                    <div class="item-list">
                        <?php 
                        $favTasks = array_filter($tasks, function($t){ return isset($t['is_favorite']) && $t['is_favorite']; });
                        $favNotes = array_filter($notes, function($n){ return isset($n['is_favorite']) && $n['is_favorite']; });
                        if(empty($favTasks) && empty($favNotes)): ?>
                            <div class="empty-state">
                                <i class="far fa-star" style="font-size: 5rem; color: #dcdfe4; margin-bottom: 1rem;"></i>
                                <h3 style="color: var(--text-secondary);">Aucun favori pour le moment</h3>
                            </div>
                        <?php else: ?>
                            <?php foreach($favTasks as $task): ?>
                                <div class="list-item">
                                    <i class="fas fa-star" style="color: #f59e0b; margin-top: 5px;"></i>
                                    <div class="item-content">
                                        <div class="item-title"><?php echo htmlspecialchars($task['title']); ?> (Tâche)</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php foreach($favNotes as $note): ?>
                                <div class="list-item">
                                    <i class="fas fa-star" style="color: #f59e0b; margin-top: 5px;"></i>
                                    <div class="item-content">
                                        <div class="item-title"><?php echo htmlspecialchars($note['title']); ?> (Note)</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
    </div>

    <!-- Modals -->
    <!-- Task Modal -->
    <div class="modal-overlay" id="task-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Ajouter une tâche</h2>
                <button class="close-modal" onclick="closeModal('task-modal')"><i class="fas fa-times"></i></button>
            </div>
            <form id="task-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Titre de la tâche</label>
                    <input type="text" name="title" required placeholder="Ex: Faire des tests">
                </div>
                
                <div class="row">
                    <div class="col form-group">
                        <label>Type</label>
                        <input type="date" name="type_date">
                    </div>
                    <div class="col form-group">
                        <label>Criticité</label>
                        <input type="date" name="crit_date">
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Décrivez la tâche en détail..."></textarea>
                </div>

                <div class="form-group">
                    <label>Priorité</label>
                    <select name="priority">
                        <option value="minimum">Minimum</option>
                        <option value="medium">Moyenne</option>
                        <option value="high">Haute</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Pièce jointe</label>
                    <div class="file-drop-area" onclick="this.querySelector('input').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p id="task-file-name">Glisser-déposer des fichiers pour les joindre ou</p>
                        <button type="button" class="btn btn-secondary" style="margin-top:0.5rem; background:#ebecf0; border:none; padding: 0.5rem 1rem;">Parcourir</button>
                        <input type="file" name="attachment" style="display: none;" onchange="document.getElementById('task-file-name').innerText = this.files[0].name">
                    </div>
                </div>

                <div class="row" style="display: none;" id="reminder-fields">
                    <div class="col form-group">
                        <label>Date d'échéance <span style="color:red">*</span></label>
                        <input type="date" name="reminder_d">
                    </div>
                    <div class="col form-group">
                        <label>Heure d'échéance <span style="color:red">*</span></label>
                        <input type="time" name="reminder_t">
                    </div>
                </div>

                <div class="form-group">
                    <label>Information <span style="color:red">*</span></label>
                    <input type="text" name="info" placeholder="Décrivez la tâche en détail...">
                </div>

                <label class="checkbox-wrapper">
                    <input type="checkbox" name="is_exceptional" id="is_exceptional_cb">
                    <span>Tâche exceptionnelle</span>
                </label>

                <label class="checkbox-wrapper">
                    <input type="checkbox" name="create_another">
                    <span>Créer une autre</span>
                </label>

                <div class="modal-footer">
                    <div></div>
                    <div class="footer-right">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('task-modal')">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Note Modal -->
    <div class="modal-overlay" id="note-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Nouvelle note</h2>
                <button class="close-modal" onclick="closeModal('note-modal')"><i class="fas fa-times-circle" style="font-size: 1.2rem;"></i></button>
            </div>
            <form id="note-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Titre de la Note</label>
                    <input type="text" name="title" required placeholder="Ex: Faire des tests">
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="content" rows="4" required placeholder="Décrivez la note en détail..."></textarea>
                </div>

                <div class="form-group">
                    <label>Pièce jointe</label>
                    <div class="file-drop-area" onclick="this.querySelector('input').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p id="note-file-name">Glisser-déposer des fichiers pour les joindre ou</p>
                        <button type="button" class="btn btn-secondary" style="margin-top:0.5rem; background:#ebecf0; border:none; padding: 0.5rem 1rem;">Parcourir</button>
                        <input type="file" name="attachment" style="display: none;" onchange="document.getElementById('note-file-name').innerText = this.files[0].name">
                    </div>
                </div>

                <div class="row">
                    <div class="col form-group">
                        <label>Date d'échéance <span style="color:red">*</span></label>
                        <input type="date" name="note_date">
                    </div>
                    <div class="col form-group">
                        <label>Heure d'échéance <span style="color:red">*</span></label>
                        <input type="time" name="note_time">
                    </div>
                </div>

                <div class="form-group">
                    <label>Information <span style="color:red">*</span></label>
                    <input type="text" name="note_info" placeholder="Décrivez la note en détail...">
                </div>

                <div class="modal-footer">
                    <div></div>
                    <div class="footer-right">
                        <button type="button" class="btn btn-secondary" style="border:1px solid var(--border-color);" onclick="closeModal('note-modal')">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- New Profile Modal (Focused on Identity) -->
    <div class="modal-overlay" id="profile-modal" style="background: rgba(0,0,0,0.6); z-index: 9999; overflow-y: auto; padding: 2rem 0;">
        <div class="modal-content" style="max-width: 450px; padding: 0; background: var(--background-color); border-radius: var(--radius-lg); overflow-y: auto; position: relative; z-index: 10000; margin: auto;">
            <div class="modal-header" style="background: var(--primary-color); color: white; border-radius: 0; padding: 1.25rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-arrow-left" style="cursor: pointer; font-size: 1.2rem; padding: 0.5rem;" onclick="closeModal('profile-modal')"></i>
                    <h2 class="modal-title" style="color: white; font-size: 1.125rem; font-weight: 600; margin: 0;">Mon Profil</h2>
                </div>
            </div>
            
            <form id="profile-form" enctype="multipart/form-data" action="api/settings.php" method="POST">
                <div style="background: var(--surface-color); padding: 2rem 0; display: flex; flex-direction: column; align-items: center; border-bottom: 1px solid var(--border-color);">
                    <div style="position: relative; width: 140px; height: 140px; margin-bottom: 1rem;">
                        <img id="profile-preview" src="<?php echo (!empty($user['pdp']) && $user['pdp'] !== 'default.png') ? 'uploads/'.$user['pdp'] : 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['user_nom']).'&background=random'; ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <label style="position: absolute; bottom: 5px; right: 5px; background: var(--primary-color); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid #fff;">
                            <i class="fas fa-camera" style="font-size: 0.9rem;"></i>
                            <input type="file" name="pdp" accept="image/*" style="display: none;" onchange="document.getElementById('profile-preview').src = window.URL.createObjectURL(this.files[0])">
                        </label>
                    </div>
                </div>

                <div style="padding: 1.5rem; background: var(--surface-color); display: flex; flex-direction: column; gap: 1.25rem;">
                    <div class="prof-group">
                        <label style="color: var(--primary-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Votre nom</label>
                        <input type="text" name="nom" value="<?php echo htmlspecialchars($_SESSION['user_nom']); ?>" style="width: 100%; border: none; border-bottom: 1px solid var(--border-color); padding: 0.5rem 0; font-size: 1rem; background: transparent; color: var(--text-primary);" required>
                    </div>

                    <div class="prof-group">
                        <label style="color: var(--primary-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Actu / Statut</label>
                        <input type="text" name="status" id="status-input-prof" value="<?php echo htmlspecialchars($user_status); ?>" style="width: 100%; border: none; border-bottom: 1px solid var(--border-color); padding: 0.5rem 0; font-size: 1rem; background: transparent; color: var(--text-primary);">
                        <div style="margin-top: 0.75rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <?php foreach(["Disponible", "Occupé", "En réunion", "Dort", "Focus"] as $s): ?>
                                <span style="background: var(--background-color); border: 1px solid var(--border-color); padding: 0.35rem 0.75rem; border-radius: 15px; font-size: 0.75rem; cursor: pointer;" onclick="document.getElementById('status-input-prof').value='<?php echo $s; ?>'"><?php echo $s; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="prof-group">
                        <label style="color: var(--primary-color); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Biographie</label>
                        <textarea name="bio" rows="2" style="width: 100%; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.75rem; font-size: 0.875rem; background: transparent; color: var(--text-primary); resize: none;"><?php echo htmlspecialchars($user_bio); ?></textarea>
                    </div>
                </div>
                <div style="padding: 1.5rem; background: var(--surface-color); border-top: 1px solid var(--border-color);">
                    <button type="submit" class="btn btn-primary" style="width: 100%; border-radius: 10px; padding: 1rem; font-weight: 600;">ENREGISTRER LE PROFIL</button>
                </div>
            </form>
        </div>
    </div>

    <!-- New Settings Modal (App & Account) -->
    <div class="modal-overlay" id="settings-modal" style="background: rgba(0,0,0,0.6); z-index: 9999;">
        <div class="modal-content" style="max-width: 500px; padding: 2rem;">
            <div class="modal-header">
                <h2 class="modal-title">Paramètres Généraux</h2>
                <button class="close-modal" onclick="closeModal('settings-modal')"><i class="fas fa-times"></i></button>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="settings-section">
                    <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Sécurité</h4>
                    <form id="password-form" class="api-form" data-api="api/settings.php">
                        <div class="form-group">
                            <label>Email du compte</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background: #f4f5f7;">
                        </div>
                        <div class="form-group">
                            <label>Nouveau mot de passe</label>
                            <input type="password" name="new_password" placeholder="Laissez vide pour ne pas changer">
                        </div>
                        <input type="hidden" name="nom" value="<?php echo htmlspecialchars($_SESSION['user_nom']); ?>">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Mettre à jour le mot de passe</button>
                    </form>
                </div>

                <div class="settings-section" style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                    <h4 style="margin-bottom: 1rem; color: var(--primary-color);">Préférences</h4>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <span>Mode Sombre / Clair</span>
                        <button class="btn btn-secondary" onclick="toggleTheme()" id="theme-btn-toggle">Changer</button>
                    </div>
                </div>

                <div class="settings-section" style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                    <a href="logout.php" class="btn btn-secondary" style="width: 100%; color: var(--danger); border-color: var(--danger);">Déconnexion</a>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toast-container"></div>

    <script src="assets/js/main.js"></script>
</body>
</html>
