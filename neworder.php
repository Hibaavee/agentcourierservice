

<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

include_once 'database.php'; // Include the Database class

$response = array();

try {
    // Create a new Database instance and get the connection
    $database = new Database();
    $conn = $database->getConnection();

    // Check if connection was successful
    if ($conn == null) {
        throw new Exception("Connection failed.");
    }

    // Decode JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Get the agent_id and delivery_status from the input
    $agent_id = $_POST['agent_id'];
    $delivery_status = $_POST['delivery_status'];

    // Validate inputs
    if (empty($agent_id)) {
        $response["msg"] = "Agent ID is missing.";
        echo json_encode($response);
        exit;
    }
    if (isset($delivery_date)) {
        // Convert to 'YYYY-MM-DD' format if needed
        $delivery_date = substr($delivery_date, 0, 10); // Get only the date part
    }
    // Prepare and execute the query to fetch orders for the given agent_id
    $stmt = $conn->prepare("
        SELECT o.agent_id, o.order_id, o.delivery_status, o.price, o.delivery_date, o.order_date,
               p.name AS pickup_name, p.username AS pickup_username, p.mobile_no AS pickup_mobile_no, 
               p.address AS pickup_address, p.area AS pickup_area, p.pincode AS pickup_pincode, 
               d.name AS delivery_name, d.username AS delivery_username, 
               d.mobile_no AS delivery_mobile_no, d.address AS delivery_address, 
               d.area AS delivery_area, d.pincode AS delivery_pincode
        FROM ordertable o
        LEFT JOIN pickup_address p ON o.pickup_id = p.pa_id
        LEFT JOIN delivery_address d ON o.delivery_id = d.delivery_id
        WHERE o.agent_id = ? AND o.delivery_status = ?
    ");
    $stmt->bindParam(1, $agent_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $delivery_status, PDO::PARAM_STR);
    $stmt->execute();

    // Fetch all results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if any records are found
    if ($results) {
        $response["msg"] = "success";
        $response["data"] = array();

        // Loop through all results and add them to the response data
        foreach ($results as $result) {
            // Format the delivery_date to include only the date
            $formatted_date = (new DateTime($result["delivery_date"]))->format('Y-m-d');

            $response["data"][] = array(
                "order_id" => $result["order_id"],
                "price" => $result["price"],
                "order_date" => $result["order_date"],
                "delivery_date" => $formatted_date,
                "delivery_status" => $result["delivery_status"],  
                "pickup_address" => array(
                    "name" => $result["pickup_name"],
                    "username" => $result["pickup_username"],
                    "mobile_no" => $result["pickup_mobile_no"],
                    "address" => $result["pickup_address"],
                    "area" => $result["pickup_area"],
                    "pincode" => $result["pickup_pincode"]
                ),
                "delivery_address" => array(
                    "name" => $result["delivery_name"],
                    "username" => $result["delivery_username"],
                    "mobile_no" => $result["delivery_mobile_no"],
                    "address" => $result["delivery_address"],
                    "area" => $result["delivery_area"],
                    "pincode" => $result["delivery_pincode"]
                )
            );
        }

        // Log success message
        error_log("Records retrieved for agent_id: $agent_id");
    } else {
        $response["msg"] = "No records found";

        // Log no records message
        error_log("No records found for agent_id: $agent_id");
    }

    // Close connection
    $conn = null;

} catch (Exception $e) {
    // Return error message in JSON
    $response["msg"] = "An error occurred: " . $e->getMessage();
    error_log("Error: " . $e->getMessage());
}

// Output JSON response
echo json_encode($response);
?>
