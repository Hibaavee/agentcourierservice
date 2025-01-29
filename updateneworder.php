<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

include_once 'database.php';

if (isset($_POST['order_id']) && isset($_POST['delivery_status'])) {
   
    $order_id = $_POST['order_id'];
     $qrcode = $_POST['qr_code'];
    $delivery_status = $_POST['delivery_status'];

    // Validate input
    if (!is_numeric($order_id) || !is_numeric($delivery_status)) {
        echo json_encode(["success" => false, "message" => "Invalid input data."]);
        exit();
    }

    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        echo json_encode(["success" => false, "message" => "Database connection failed."]);
        exit();
    }

    // Check if the delivery_status is 2, which means the order is delivered
    if ($delivery_status == 2) {
        // Get the current date for delivery_date
        $delivery_date = date('Y-m-d'); // You can customize the date format if needed

        // Query to update both delivery_status and delivery_date for the order
        $sql = "UPDATE ordertable SET delivery_status = :delivery_status, delivery_date = :delivery_date WHERE order_id = :order_id";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':delivery_status', $delivery_status, PDO::PARAM_INT);
        $stmt->bindParam(':delivery_date', $delivery_date, PDO::PARAM_STR);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);

        $response = [];

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Order status updated to Completed with delivery date: $delivery_date";
        } else {
            $response['success'] = false;
            $response['message'] = "Failed to update order status or delivery date.";
        }

        echo json_encode($response);
        exit();
    }

    // If delivery_status is not 2, just update the delivery_status
    $sql = "UPDATE ordertable SET delivery_status = :delivery_status WHERE order_id = :order_id";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':delivery_status', $delivery_status, PDO::PARAM_INT);
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);

    $response = [];

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = $delivery_status == 1 ? 
            "Order status updated to Dispatched." : 
            "Order status updated to Completed.";
    } else {
        $response['success'] = false;
        $response['message'] = "Failed to update order status.";
    }

    echo json_encode($response);

} else {
    echo json_encode(["success" => false, "message" => "Missing order_id or delivery_status"]);
}
?>

