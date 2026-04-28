<?php
// ============================================================
//  ADD THESE ENDPOINTS TO YOUR EXISTING api.php
//  Paste these BEFORE the final "else { fail(...) }" block
// ============================================================

// ── GET EQUIPMENT BY QR CODE ──────────────────────────────
// Handles QR codes like "EQ-001", "EQ-012" etc.
elseif ($action === 'get_equipment_by_qr') {
    $code = trim($_GET['code'] ?? '');
    if (!$code) fail("Code is required");

    // Extract number from QR code (e.g. "EQ-001" → 1)
    $id = (int)preg_replace('/[^0-9]/', '', $code);
    if (!$id) fail("Invalid QR code format");

    $stmt = $conn->prepare("SELECT * FROM equipment WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) fail("Equipment not found");

    $row['total']     = (int)$row['total'];
    $row['available'] = (int)$row['available'];
    respond($row);
}

// ── GET EQUIPMENT BY ID ───────────────────────────────────
elseif ($action === 'get_equipment_by_id') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) fail("ID is required");

    $stmt = $conn->prepare("SELECT * FROM equipment WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) fail("Equipment not found");

    $row['total']     = (int)$row['total'];
    $row['available'] = (int)$row['available'];
    respond($row);
}

// ── GET ACTIVE BORROW FOR EQUIPMENT ──────────────────────
elseif ($action === 'get_active_borrow') {
    $eqId = (int)($_GET['equipment_id'] ?? 0);
    if (!$eqId) fail("Equipment ID is required");

    $stmt = $conn->prepare("
        SELECT b.*, e.name AS equipment_name
        FROM borrowers b
        JOIN equipment e ON b.equipment_id = e.id
        WHERE b.equipment_id = ? AND b.status = 'Borrowed'
        ORDER BY b.id DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $eqId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) respond([]); // No active borrow

    $row['quantity'] = (int)$row['quantity'];
    respond($row);
}
