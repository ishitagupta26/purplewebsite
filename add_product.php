<?php
// Database connection
$conn = new mysqli('localhost', 'username', 'password', 'your_database_name');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate and sanitize input
$product_name = trim($_POST['product_name']);
$product_price = trim($_POST['product_price']);
$product_image = $_FILES['product_image']['name'];
$target_dir = "uploads/";

// Ensure inputs are sanitized to prevent SQL injection
$product_name = $conn->real_escape_string($product_name);
$product_price = floatval($product_price); // Cast price to a float for safety

// Validate image upload
if (!empty($product_image)) {
    $target_file = $target_dir . basename($product_image);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Allow only certain file types
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        echo "Only JPG, JPEG, PNG, and GIF files are allowed.";
        exit;
    }

    // Limit file size to 2MB
    if ($_FILES['product_image']['size'] > 2 * 1024 * 1024) {
        echo "File size should not exceed 2MB.";
        exit;
    }

    // Upload the file
    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
        // Insert product into the database
        $sql = "INSERT INTO products (name, price, image) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sds", $product_name, $product_price, $product_image);

        if ($stmt->execute()) {
            echo "Product added successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error uploading image.";
    }
} else {
    echo "Please upload a product image.";
}

$conn->close();

// Redirect to admin dashboard
header("Location: admin_dashboard.php");
exit;
?>
