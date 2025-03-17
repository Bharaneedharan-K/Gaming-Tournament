<?php
require_once 'config/database.php';
require_once 'includes/header.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Verify tournament ownership
$stmt = $db->prepare("SELECT tournament_id FROM tournaments WHERE tournament_id = ? AND owner_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
if ($stmt->rowCount() == 0) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Handle participant approval/denial
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $participant_id = $_POST['participant_id'];
    $action = $_POST['action'];

    if ($action == 'approve' || $action == 'deny') {
        $stmt = $db->prepare("UPDATE tournament_participants SET is_approved = ? WHERE participant_id = ? AND tournament_id = ?");
        $is_approved = $action == 'approve' ? 1 : 0;
        
        if ($stmt->execute([$is_approved, $participant_id, $_GET['id']])) {
            $success = "Participant " . ($action == 'approve' ? 'approved' : 'denied') . " successfully!";
        } else {
            $error = "Failed to update participant status.";
        }
    }
}

// Get pending participants
$stmt = $db->prepare("SELECT tp.*, u.username, u.email 
                    FROM tournament_participants tp 
                    JOIN users u ON tp.user_id = u.user_id 
                    WHERE tp.tournament_id = ? AND tp.is_approved = 0");
$stmt->execute([$_GET['id']]);
$pending_participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get approved participants
$stmt = $db->prepare("SELECT tp.*, u.username, u.email 
                    FROM tournament_participants tp 
                    JOIN users u ON tp.user_id = u.user_id 
                    WHERE tp.tournament_id = ? AND tp.is_approved = 1");
$stmt->execute([$_GET['id']]);
$approved_participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get tournament details
$stmt = $db->prepare("SELECT * FROM tournaments WHERE tournament_id = ?");
$stmt->execute([$_GET['id']]);
$tournament = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Manage Participants - <?php echo htmlspecialchars($tournament['tournament_name']); ?></h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <h5 class="mb-3">Pending Approvals</h5>
                <?php if (count($pending_participants) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-dark">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <?php if ($tournament['is_team_based']): ?>
                                        <th>Team Name</th>
                                    <?php endif; ?>
                                    <?php if ($tournament['is_paid']): ?>
                                        <th>Transaction ID</th>
                                    <?php endif; ?>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_participants as $participant): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($participant['username']); ?></td>
                                        <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                        <?php if ($tournament['is_team_based']): ?>
                                            <td><?php echo htmlspecialchars($participant['team_name']); ?></td>
                                        <?php endif; ?>
                                        <?php if ($tournament['is_paid']): ?>
                                            <td><?php echo htmlspecialchars($participant['transaction_id']); ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="participant_id" value="<?php echo $participant['participant_id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                            </form>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="participant_id" value="<?php echo $participant['participant_id']; ?>">
                                                <input type="hidden" name="action" value="deny">
                                                <button type="submit" class="btn btn-danger btn-sm">Deny</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No pending approvals.</p>
                <?php endif; ?>

                <h5 class="mb-3 mt-4">Approved Participants</h5>
                <?php if (count($approved_participants) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-dark">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <?php if ($tournament['is_team_based']): ?>
                                        <th>Team Name</th>
                                    <?php endif; ?>
                                    <?php if ($tournament['is_paid']): ?>
                                        <th>Transaction ID</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approved_participants as $participant): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($participant['username']); ?></td>
                                        <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                        <?php if ($tournament['is_team_based']): ?>
                                            <td><?php echo htmlspecialchars($participant['team_name']); ?></td>
                                        <?php endif; ?>
                                        <?php if ($tournament['is_paid']): ?>
                                            <td><?php echo htmlspecialchars($participant['transaction_id']); ?></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No approved participants yet.</p>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="tournament_details.php?id=<?php echo $_GET['id']; ?>" class="btn btn-primary">Back to Tournament</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 