<?php
include 'db_connect.php';

// =======================
// CREATE PRODUCT
// =======================
if (isset($_POST['add'])) {
    $stmt = $conn->prepare("INSERT INTO products (product_name, category, price, total_sales) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdd", $_POST['product_name'], $_POST['category'], $_POST['price'], $_POST['total_sales']);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// =======================
// DELETE PRODUCT
// =======================
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// =======================
// UPDATE PRODUCT
// =======================
if (isset($_POST['update'])) {
    $stmt = $conn->prepare("UPDATE products SET product_name=?, category=?, price=?, total_sales=? WHERE id=?");
    $stmt->bind_param("ssddi", $_POST['product_name'], $_POST['category'], $_POST['price'], $_POST['total_sales'], $_POST['id']);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// =======================
// ANALYTICS
// =======================

// Average sales
$avgQuery = $conn->query("SELECT AVG(total_sales) AS avg_sales FROM products");
$avgRow = $avgQuery->fetch_assoc();
$averageSales = $avgRow['avg_sales'] ?? 0;

// Top 3 best-selling products (fixed for MariaDB)
$topProducts = $conn->query("
    SELECT product_name, total_sales
    FROM products
    ORDER BY total_sales DESC
    LIMIT 3
");

// Products above average
$aboveAverage = $conn->query("
    SELECT product_name, total_sales 
    FROM products 
    WHERE total_sales > (SELECT AVG(total_sales) FROM products)
");

// Fetch all products for display
$allProducts = $conn->query("SELECT * FROM products ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Performance Management System</title>
    <link rel="stylesheet" href="style.css">
 
</head>
<body>

<h2>📊 Sales Performance Management System</h2>

<!-- ADD PRODUCT FORM -->
<form method="POST">
    <h3>Add New Product</h3>
    <input type="text" name="product_name" placeholder="Product Name" required>
    <input type="text" name="category" placeholder="Category" required>
    <input type="number" step="0.01" name="price" placeholder="Price" required>
    <input type="number" step="0.01" name="total_sales" placeholder="Total Sales" required>
    <button type="submit" name="add">Add Product</button>
</form>

<!-- PRODUCTS TABLE -->
<table>
    <tr>
        <th>ID</th>
        <th>Product</th>
        <th>Category</th>
        <th>Price</th>
        <th>Total Sales</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $allProducts->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['product_name'] ?></td>
        <td><?= $row['category'] ?></td>
        <td>₱<?= number_format($row['price'], 2) ?></td>
        <td>₱<?= number_format($row['total_sales'], 2) ?></td>
        <td>
            <form method="POST" style="display:inline-block;">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <input type="text" name="product_name" value="<?= $row['product_name'] ?>" required>
                <input type="text" name="category" value="<?= $row['category'] ?>" required>
                <input type="number" step="0.01" name="price" value="<?= $row['price'] ?>" required>
                <input type="number" step="0.01" name="total_sales" value="<?= $row['total_sales'] ?>" required>
                <button type="submit" name="update">Update</button>
            </form>
            <a href="index.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this record?')">🗑 Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- ANALYTICS SECTION -->
<div class="section">
    <h3>💡 Analytics</h3>
    <p><strong>Average Sales:</strong> ₱<?= number_format($averageSales, 2) ?></p>

    <h4>Top 3 Best-Selling Products</h4>
    <ul>
        <?php while ($top = $topProducts->fetch_assoc()): ?>
            <li><?= $top['product_name'] ?> — ₱<?= number_format($top['total_sales'], 2) ?></li>
        <?php endwhile; ?>
    </ul>

    <h4>Products Above Average Sales</h4>
    <ul>
        <?php while ($aa = $aboveAverage->fetch_assoc()): ?>
            <li><?= $aa['product_name'] ?> — ₱<?= number_format($aa['total_sales'], 2) ?></li>
        <?php endwhile; ?>
    </ul>
</div>

</body>
</html>
