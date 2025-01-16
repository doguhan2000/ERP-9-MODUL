<?php
require_once '../../config/db.php';

// Müşterileri getir (modal için)
$customers = $conn->query("SELECT id, name, company_name FROM customers WHERE status = 'active' ORDER BY name")->fetchAll();

// Notları getir
$notes = $conn->query("
    SELECT 
        n.*,
        c.name as customer_name,
        c.company_name
    FROM notes n
    LEFT JOIN customers c ON n.customer_id = c.id
    WHERE n.status = 'active'
    ORDER BY n.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notlar - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 main-sidebar">
                <?php include 'includes/sidebar.php'; ?>
            </div>

            <!-- Ana İçerik -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Notlar</h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newNoteModal">
                        <i class="bi bi-journal-plus"></i> Yeni Not
                    </button>
                </div>

                <!-- Not Listesi -->
                <div class="row">
                    <?php foreach ($notes as $note): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><?= htmlspecialchars($note['title']) ?></h6>
                                <span class="badge bg-<?= $note['type'] == 'meeting' ? 'primary' : 
                                    ($note['type'] == 'complaint' ? 'danger' : 'info') ?>">
                                    <?= $note['type'] == 'meeting' ? 'Görüşme' : 
                                        ($note['type'] == 'complaint' ? 'Şikayet' : 'Genel') ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?= nl2br(htmlspecialchars($note['content'])) ?></p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <strong>Müşteri:</strong> <?= htmlspecialchars($note['customer_name']) ?>
                                        <?php if ($note['company_name']): ?>
                                            (<?= htmlspecialchars($note['company_name']) ?>)
                                        <?php endif; ?>
                                    </small>
                                </p>
                            </div>
                            <div class="card-footer text-muted d-flex justify-content-between align-items-center">
                                <small><?= date('d.m.Y H:i', strtotime($note['created_at'])) ?></small>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editNote(<?= $note['id'] ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteNote(<?= $note['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Yeni Not Modalı -->
    <div class="modal fade" id="newNoteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Not Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newNoteForm">
                        <div class="mb-3">
                            <label class="form-label">Müşteri</label>
                            <select class="form-select" name="customer_id" required>
                                <option value="">Müşteri Seçin</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>">
                                        <?= htmlspecialchars($customer['name']) ?> 
                                        <?= $customer['company_name'] ? '(' . htmlspecialchars($customer['company_name']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Başlık</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İçerik</label>
                            <textarea class="form-control" name="content" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tip</label>
                            <select class="form-select" name="type" required>
                                <option value="meeting">Görüşme</option>
                                <option value="complaint">Şikayet</option>
                                <option value="general">Genel</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-success" onclick="saveNote()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Not Detay Modalı -->
    <div class="modal fade" id="noteDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Not Detayları</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="noteDetailsContent">
                </div>
            </div>
        </div>
    </div>

    <!-- Not Düzenleme Modalı -->
    <div class="modal fade" id="editNoteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Notu Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editNoteForm">
                        <input type="hidden" name="note_id" id="editNoteId">
                        <div class="mb-3">
                            <label class="form-label">Başlık</label>
                            <input type="text" class="form-control" name="title" id="editNoteTitle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İçerik</label>
                            <textarea class="form-control" name="content" id="editNoteContent" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tip</label>
                            <select class="form-select" name="type" id="editNoteType" required>
                                <option value="meeting">Görüşme</option>
                                <option value="complaint">Şikayet</option>
                                <option value="general">Genel</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="updateNote()">Güncelle</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function saveNote() {
        const form = document.getElementById('newNoteForm');
        const formData = new FormData(form);

        fetch('api/add_note.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            alert('Bir hata oluştu: ' + error);
        });
    }

    function viewNote(noteId) {
        fetch(`api/get_note_details.php?id=${noteId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const note = data.note;
                    const tipTurkce = {
                        'meeting': 'Görüşme',
                        'complaint': 'Şikayet',
                        'general': 'Genel'
                    };
                    const html = `
                        <p><strong>Başlık:</strong> ${note.title}</p>
                        <p><strong>Müşteri:</strong> ${note.customer_name}</p>
                        <p><strong>İçerik:</strong> ${note.content}</p>
                        <p><strong>Tip:</strong> ${tipTurkce[note.type]}</p>
                        <p><strong>Oluşturulma:</strong> ${new Date(note.created_at).toLocaleString('tr-TR')}</p>
                    `;
                    document.getElementById('noteDetailsContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('noteDetailsModal')).show();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                alert('Bir hata oluştu: ' + error);
            });
    }

    function editNote(noteId) {
        fetch(`api/get_note_details.php?id=${noteId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const note = data.note;
                    document.getElementById('editNoteId').value = note.id;
                    document.getElementById('editNoteTitle').value = note.title;
                    document.getElementById('editNoteContent').value = note.content;
                    document.getElementById('editNoteType').value = note.type;
                    new bootstrap.Modal(document.getElementById('editNoteModal')).show();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                alert('Bir hata oluştu: ' + error);
            });
    }

    function updateNote() {
        const form = document.getElementById('editNoteForm');
        const formData = new FormData(form);

        fetch('api/update_note.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            alert('Bir hata oluştu: ' + error);
        });
    }

    function deleteNote(noteId) {
        if (!confirm('Bu notu silmek istediğinize emin misiniz?')) {
            return;
        }

        fetch('api/delete_note.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ note_id: noteId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            alert('Bir hata oluştu: ' + error);
        });
    }
    </script>
</body>
</html> 