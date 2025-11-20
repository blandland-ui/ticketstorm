<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    jsonResponse(false, 'Authentication required');
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        handleCreateTicket();
        break;
    case 'list':
        handleListTickets();
        break;
    case 'get':
        handleGetTicket();
        break;
    case 'update':
        handleUpdateTicket();
        break;
    case 'comment':
        handleAddComment();
        break;
    case 'stats':
        handleGetStats();
        break;
    default:
        jsonResponse(false, 'Invalid action');
}

function handleCreateTicket() {
    $subject = $_POST['subject'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'Medium';
    $category = $_POST['category'] ?? 'General';
    
    if (empty($subject) || empty($description)) {
        jsonResponse(false, 'Subject and description are required');
    }
    
    $user_id = $_SESSION['user_id'];
    $conn = Database::getTicketsConnection();
    
    $stmt = $conn->prepare("INSERT INTO tickets (user_id, subject, description, priority, category) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $subject, $description, $priority, $category);
    
    if ($stmt->execute()) {
        $ticket_id = $stmt->insert_id;
        jsonResponse(true, 'Ticket created successfully', ['ticket_id' => $ticket_id]);
    } else {
        jsonResponse(false, 'Failed to create ticket');
    }
}

function handleListTickets() {
    $user_id = $_SESSION['user_id'];
    $is_admin = $_SESSION['is_admin'];
    $status_filter = $_GET['status'] ?? '';
    
    $conn = Database::getTicketsConnection();
    $users_conn = Database::getUsersConnection();
    
    // Build query based on admin status
    if ($is_admin) {
        $query = "SELECT t.*, u.username, u.full_name, u.email 
                  FROM tickets t 
                  LEFT JOIN " . DB_USERS . ".users u ON t.user_id = u.user_id";
        
        if (!empty($status_filter)) {
            $query .= " WHERE t.status = ?";
        }
        $query .= " ORDER BY t.submitted_at DESC";
        
        $stmt = $conn->prepare($query);
        if (!empty($status_filter)) {
            $stmt->bind_param("s", $status_filter);
        }
    } else {
        $query = "SELECT * FROM tickets WHERE user_id = ?";
        if (!empty($status_filter)) {
            $query .= " AND status = ?";
        }
        $query .= " ORDER BY submitted_at DESC";
        
        $stmt = $conn->prepare($query);
        if (!empty($status_filter)) {
            $stmt->bind_param("is", $user_id, $status_filter);
        } else {
            $stmt->bind_param("i", $user_id);
        }
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $tickets = [];
    
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
    
    jsonResponse(true, 'Tickets retrieved', $tickets);
}

function handleGetTicket() {
    $ticket_id = $_GET['ticket_id'] ?? 0;
    $user_id = $_SESSION['user_id'];
    $is_admin = $_SESSION['is_admin'];
    
    if (empty($ticket_id)) {
        jsonResponse(false, 'Ticket ID required');
    }
    
    $conn = Database::getTicketsConnection();
    
    // Get ticket details
    $query = "SELECT t.*, u.username, u.full_name, u.email 
              FROM tickets t 
              LEFT JOIN " . DB_USERS . ".users u ON t.user_id = u.user_id 
              WHERE t.ticket_id = ?";
    
    if (!$is_admin) {
        $query .= " AND t.user_id = ?";
    }
    
    $stmt = $conn->prepare($query);
    if ($is_admin) {
        $stmt->bind_param("i", $ticket_id);
    } else {
        $stmt->bind_param("ii", $ticket_id, $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        jsonResponse(false, 'Ticket not found');
    }
    
    $ticket = $result->fetch_assoc();
    
    // Get comments
    $stmt = $conn->prepare("SELECT c.*, u.username, u.full_name, u.is_admin 
                           FROM ticket_comments c 
                           LEFT JOIN " . DB_USERS . ".users u ON c.user_id = u.user_id 
                           WHERE c.ticket_id = ? 
                           ORDER BY c.created_at ASC");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $comments_result = $stmt->get_result();
    
    $comments = [];
    while ($comment = $comments_result->fetch_assoc()) {
        $comments[] = $comment;
    }
    
    $ticket['comments'] = $comments;
    
    jsonResponse(true, 'Ticket retrieved', $ticket);
}

function handleUpdateTicket() {
    if (!isAdmin()) {
        jsonResponse(false, 'Admin access required');
    }
    
    $ticket_id = $_POST['ticket_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    $priority = $_POST['priority'] ?? '';
    
    if (empty($ticket_id)) {
        jsonResponse(false, 'Ticket ID required');
    }
    
    $conn = Database::getTicketsConnection();
    
    // Check if ticket is being resolved
    $resolved_at = null;
    if ($status === 'Resolved' || $status === 'Closed') {
        $resolved_at = date('Y-m-d H:i:s');
    }
    
    if (!empty($status) && !empty($priority)) {
        $stmt = $conn->prepare("UPDATE tickets SET status = ?, priority = ?, resolved_at = ? WHERE ticket_id = ?");
        $stmt->bind_param("sssi", $status, $priority, $resolved_at, $ticket_id);
    } elseif (!empty($status)) {
        $stmt = $conn->prepare("UPDATE tickets SET status = ?, resolved_at = ? WHERE ticket_id = ?");
        $stmt->bind_param("ssi", $status, $resolved_at, $ticket_id);
    } elseif (!empty($priority)) {
        $stmt = $conn->prepare("UPDATE tickets SET priority = ? WHERE ticket_id = ?");
        $stmt->bind_param("si", $priority, $ticket_id);
    } else {
        jsonResponse(false, 'No update parameters provided');
    }
    
    if ($stmt->execute()) {
        jsonResponse(true, 'Ticket updated successfully');
    } else {
        jsonResponse(false, 'Failed to update ticket');
    }
}

function handleAddComment() {
    $ticket_id = $_POST['ticket_id'] ?? 0;
    $comment_text = $_POST['comment'] ?? '';
    $user_id = $_SESSION['user_id'];
    $is_admin = $_SESSION['is_admin'];
    
    if (empty($ticket_id) || empty($comment_text)) {
        jsonResponse(false, 'Ticket ID and comment are required');
    }
    
    $conn = Database::getTicketsConnection();
    
    // Verify user has access to this ticket
    if (!$is_admin) {
        $stmt = $conn->prepare("SELECT user_id FROM tickets WHERE ticket_id = ?");
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            jsonResponse(false, 'Ticket not found');
        }
        
        $ticket = $result->fetch_assoc();
        if ($ticket['user_id'] != $user_id) {
            jsonResponse(false, 'Access denied');
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment_text, is_admin_response) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $ticket_id, $user_id, $comment_text, $is_admin);
    
    if ($stmt->execute()) {
        jsonResponse(true, 'Comment added successfully');
    } else {
        jsonResponse(false, 'Failed to add comment');
    }
}

function handleGetStats() {
    if (!isAdmin()) {
        jsonResponse(false, 'Admin access required');
    }
    
    $conn = Database::getTicketsConnection();
    $users_conn = Database::getUsersConnection();
    
    // Get ticket counts by status
    $stmt = $conn->query("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
    $status_counts = [];
    while ($row = $stmt->fetch_assoc()) {
        $status_counts[$row['status']] = $row['count'];
    }
    
    // Get total tickets
    $total_tickets = $conn->query("SELECT COUNT(*) as count FROM tickets")->fetch_assoc()['count'];
    
    // Get total users
    $total_users = $users_conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0")->fetch_assoc()['count'];
    
    // Get recent tickets
    $stmt = $conn->query("SELECT COUNT(*) as count FROM tickets WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recent_tickets = $stmt->fetch_assoc()['count'];
    
    jsonResponse(true, 'Stats retrieved', [
        'total_tickets' => $total_tickets,
        'total_users' => $total_users,
        'recent_tickets' => $recent_tickets,
        'status_counts' => $status_counts
    ]);
}
?>
