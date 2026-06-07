<?php
include("../config/db.php");

// session managed by config/db.php

// Admin auth check
if (!isset($_SESSION['admin_id'])) {
    header("Location: /auth/login.php");
    exit;
}

// Only accept POST requests for state changes
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /admin/dashboard.php");
    exit;
}

// CSRF validation
csrf_verify();

$action = htmlspecialchars($_POST['action'] ?? '');
$id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || !in_array($action, ['accept', 'reject'])) {
    header("Location: /admin/dashboard.php");
    exit;
}

// ── Update request status using prepared statement ───────────────────────
$new_status = ($action === 'accept') ? 'Accepted' : 'Rejected';

$stmt = mysqli_prepare($conn,
    "UPDATE Adoption_Request SET Status = ? WHERE RequestID = ?"
);
mysqli_stmt_bind_param($stmt, "si", $new_status, $id);
mysqli_stmt_execute($stmt);

// ── If accepted, mark animal as Adopted and reject other pending requests ─
if ($action === 'accept') {
    // Resolve the animal tied to this request
    $aq = mysqli_prepare($conn, "SELECT AnimalID FROM Adoption_Request WHERE RequestID = ?");
    mysqli_stmt_bind_param($aq, "i", $id);
    mysqli_stmt_execute($aq);
    $animal    = mysqli_fetch_assoc(mysqli_stmt_get_result($aq));
    $animal_id = $animal['AnimalID'] ?? null;

    if ($animal_id) {
        // Mark the animal as Adopted
        $stmt2 = mysqli_prepare($conn, "UPDATE Animal SET Status = 'Adopted' WHERE AnimalID = ?");
        mysqli_stmt_bind_param($stmt2, "i", $animal_id);
        mysqli_stmt_execute($stmt2);

        // Reject every other still-pending request for the same animal
        $stmt3 = mysqli_prepare($conn,
            "UPDATE Adoption_Request SET Status = 'Rejected'
             WHERE AnimalID = ? AND RequestID != ? AND Status = 'Pending'"
        );
        mysqli_stmt_bind_param($stmt3, "ii", $animal_id, $id);
        mysqli_stmt_execute($stmt3);
    }
}

header("Location: /admin/dashboard.php");
exit;
