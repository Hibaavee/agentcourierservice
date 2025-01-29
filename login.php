<?php

include_once 'database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Database connection
$databaseService = new Database();
$conn = $databaseService->getConnection();

// Get input data
$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;

if ($username && $password) {
    // Query to check login credentials
    $query = "SELECT * FROM agent_login WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(1, $username);
    $stmt->bindParam(2, $password);
    $stmt->execute();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $agent_id = $result['agent_id'];

            // Query to check if the agent is active
            $query = "SELECT * FROM agent_table WHERE is_active = 1 AND agent_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, $agent_id);
            $stmt->execute();
            $activeAgent = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($activeAgent) {
                // Agent is active
                echo json_encode(array(
                    "msg" => "success",
                    "agent_id" => $activeAgent['agent_id'],
                    "name" => $activeAgent['name'],
                    "error"=>""
                ));
            } else {
                // Agent is not active
                echo json_encode(array(
                    "msg" => "failed",
                    "error" => "Agent is not active"
                ));
            }
        }
    } else {
        // Invalid login credentials
        echo json_encode(array(
            "msg" => "failed",
            "error" => "Invalid username or password"
        ));
    }
} else {
    // Missing username or password
    echo json_encode(array(
        "msg" => "failed",
        "error" => "Username and password are required"
    ));
}
?>