<?php
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_database_name";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getTourPackageDetails($conn, $package_id = 1) {
    $sql = "SELECT * FROM tour_packages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
    $stmt->close();
}

function getIncludedItems($conn, $package_id = 1) {
    $sql = "SELECT item FROM inclusions WHERE package_id = ? AND included = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row["item"];
        }
    }
    $stmt->close();
    return $items;
}

function getNotIncludedItems($conn, $package_id = 1) {
    $sql = "SELECT item FROM inclusions WHERE package_id = ? AND included = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row["item"];
        }
    }
    $stmt->close();
    return $items;
}

function getItineraryItems($conn, $package_id = 1) {
    $sql = "SELECT title, description, estimated_time FROM itinerary WHERE package_id = ? ORDER BY day_order";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    $stmt->close();
    return $items;
}

function getReviews($conn, $package_id = 1) {
    $sql = "SELECT r.review_text, u.name AS reviewer_name, u.country AS reviewer_country, u.profile_picture AS reviewer_pic 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.package_id = ? 
            ORDER BY r.created_at DESC 
            LIMIT 2";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
    }
    $stmt->close();
    return $reviews;
}

function getAllDestinations($conn) {
    $sql = "SELECT DISTINCT destination FROM tour_packages";
    $result = $conn->query($sql);
    $destinations = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $destinations[] = $row["destination"];
        }
    }
    return $destinations;
}

function getPopularDestinations($conn, $limit = 4) {
    $sql = "SELECT * FROM tour_packages ORDER BY RAND() LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $destinations = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $destinations[] = $row;
        }
    }
    $stmt->close();
    return $destinations;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['book_now'])) {
        $user_id = 1;
        $package_id = 1;
        $booking_date = date("Y-m-d H:i:s");
        $status = "pending";

        $sql = "INSERT INTO bookings (user_id, package_id, booking_date, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $user_id, $package_id, $booking_date, $status);

        if ($stmt->execute() === TRUE) {
            echo "<script>alert('Booking successful!');</script>";
        } else {
            echo "<script>alert('Error booking: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}

$packageDetails = getTourPackageDetails($conn);
$includedItems = getIncludedItems($conn);
$notIncludedItems = getNotIncludedItems($conn);
$itineraryItems = getItineraryItems($conn);
$reviews = getReviews($conn);
$popularDestinations = getPopularDestinations($conn);
$allDestinations = getAllDestinations($conn);

$conn->close();
?>