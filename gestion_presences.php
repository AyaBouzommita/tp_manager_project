<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer l'ID de la séance depuis l'URL
$seance_id = isset($_GET['seance_id']) ? intval($_GET['seance_id']) : 0;

if ($seance_id === 0) {
    die("ID de séance non valide");
}

// Traitement de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);
    
    // Vérifier si l'étudiant est déjà marqué absent pour cette séance
    $check_query = "SELECT id FROM presences WHERE etudiant_id = ? AND seance_id = ?";
    $stmt = $pdo->prepare($check_query);
    $stmt->execute([$student_id, $seance_id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        // Ajouter l'absence
        $insert_query = "INSERT INTO presences (etudiant_id, seance_id, presence) VALUES (?, ?, 1)";
        $stmt = $pdo->prepare($insert_query);
        $stmt->execute([$student_id, $seance_id]);

        // Mettre à jour le total des absences
        $update_total = "UPDATE etudiants SET total = total + 1 WHERE id = ?";
        $stmt = $pdo->prepare($update_total);
        $stmt->execute([$student_id]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Étudiant déjà marqué absent pour cette séance']);
    }
    exit();
}

// Récupérer les informations de la séance
$query = "SELECT s.*, m.nom as module_name 
          FROM seances s 
          JOIN modules m ON s.module_id = m.id 
          WHERE s.id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$seance_id]);
$seance = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$seance) {
    die("Séance non trouvée");
}

// Récupérer la liste des étudiants avec leur nombre total d'absences
$query = "SELECT e.*, 
          (SELECT COUNT(*) FROM presences p WHERE p.etudiant_id = e.id AND p.presence = 1) as total_absences
          FROM etudiants e
          ORDER BY e.nom, e.prenom";
$stmt = $pdo->prepare($query);
$stmt->execute();
$etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les absences pour cette séance
$query = "SELECT etudiant_id FROM presences WHERE seance_id = ? AND presence = 1";
$stmt = $pdo->prepare($query);
$stmt->execute([$seance_id]);
$absences = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des présences</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Gestion des présences</h1>
        <h2>Séance du <?php echo htmlspecialchars($seance['date']); ?> - <?php echo htmlspecialchars($seance['module_name']); ?></h2>

        <table class="presence-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Total absences</th>
                    <th>Absent</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $etudiant): ?>
                <tr>
                    <td><?php echo htmlspecialchars($etudiant['nom']); ?></td>
                    <td><?php echo htmlspecialchars($etudiant['prenom']); ?></td>
                    <td class="total-absences-<?php echo $etudiant['id']; ?>"><?php echo $etudiant['total_absences']; ?></td>
                    <td>
                        <input type="checkbox" 
                               class="absence-checkbox" 
                               data-student-id="<?php echo $etudiant['id']; ?>"
                               <?php echo in_array($etudiant['id'], $absences) ? 'checked disabled' : ''; ?>>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    $(document).ready(function() {
        $('.absence-checkbox').change(function() {
            if ($(this).is(':checked')) {
                const studentId = $(this).data('student-id');
                const checkbox = $(this);
                
                $.ajax({
                    url: 'gestion_presences.php?seance_id=<?php echo $seance_id; ?>',
                    method: 'POST',
                    data: { student_id: studentId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            checkbox.prop('disabled', true);
                            // Mettre à jour le total des absences
                            const totalCell = $('.total-absences-' + studentId);
                            const currentTotal = parseInt(totalCell.text());
                            totalCell.text(currentTotal + 1);
                        } else {
                            alert(response.message);
                            checkbox.prop('checked', false);
                        }
                    },
                    error: function() {
                        alert('Erreur lors de l\'enregistrement de l\'absence');
                        checkbox.prop('checked', false);
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
                        const totalCell = $('.total-absences-' + studentId);
                        const currentTotal = parseInt(totalCell.text());
                        if (action === 'add') {
                            totalCell.text(currentTotal + 1);
                        } else {
                            totalCell.text(currentTotal - 1);
                        }
                    } else {
                        alert(response.message);
                        checkbox.prop('checked', action === 'remove');
                    }
                },
                error: function() {
                    alert('Erreur lors de la modification de l\'absence');
                    checkbox.prop('checked', action === 'remove');
                }
            });
        });
    });
    </script>
</body>
</html>
