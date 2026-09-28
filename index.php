<?php
// index.php - Logika CRUD & Tampilan Tema Pink & Kuning
require_once 'db.php';
require_once 'csrf.php';

$errors = [];
$success = isset($_GET['success']) ? $_GET['success'] : '';

// 1. PROCESS SAVE (INSERT / UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Token CSRF tidak valid.');
    }

    $id       = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name     = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price    = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock    = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

    // Validasi Input
    if (mb_strlen($name) < 3) {
        $errors[] = "Nama produk minimal harus 3 karakter.";
    }

    if ($price === false || $price <= 0) {
        $errors[] = "Harga harus angka bernilai lebih dari 0.";
    }

    if ($stock === false || $stock < 0) {
        $errors[] = "Stok harus angka bernilai 0 atau lebih.";
    }

    // Cek Nama Unik
    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name = ? AND id != ?");
            $stmt->execute([$name, $id]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name = ?");
            $stmt->execute([$name]);
        }
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Nama produk sudah digunakan. Gunakan nama unik.";
        }
    }

    // Simpan ke Database
    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, category = ?, price = ?, stock = ? WHERE id = ?");
            $stmt->execute([$name, $category, $price, $stock, $id]);
            $msg = "Produk berhasil diperbarui.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $category, $price, $stock]);
            $msg = "Produk berhasil ditambahkan.";
        }

        header("Location: index.php?success=" . urlencode($msg));
        exit;
    }
}

// 2. PROCESS DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_delete'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Token CSRF tidak valid.');
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?success=" . urlencode("Produk berhasil dihapus."));
        exit;
    }
}

// 3. FETCH ALL PRODUCTS
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();

// 4. FETCH EDIT PRODUCT DATA
$editProduct = null;
if (isset($_GET['edit'])) {
    $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($editId) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$editId]);
        $editProduct = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Manager - Pink & Yellow Theme</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            background-color: #ffe6f0 !important; /* Background Soft Pink */
            color: #333333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        /* Header Banner (Gradient Pink & Kuning) */
        header {
            background: linear-gradient(135deg, #ff4081, #ffeb3b) !important;
            padding: 28px;
            border-radius: 16px;
            color: #ffffff;
            text-align: center;
            box-shadow: 0 4px 15px rgba(255, 64, 129, 0.3);
            margin-bottom: 24px;
        }
        header h1 {
            margin: 0;
            font-size: 30px;
            font-weight: 800;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        header p {
            margin: 6px 0 0;
            font-size: 15px;
            color: #fffde7;
            font-weight: 600;
        }
        /* Alerts */
        .alert-success {
            background-color: #fffde7;
            border-left: 6px solid #fbc02d;
            color: #f57f17;
            padding: 14px;
            border-radius: 8px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .alert-error {
            background-color: #ffe0b2;
            border-left: 6px solid #e65100;
            color: #bf360c;
            padding: 14px;
            border-radius: 8px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        /* Form Card */
        .form-card {
            background-color: #ffffff !important;
            padding: 24px;
            border-radius: 16px;
            border: 2px solid #ff80ab !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            margin-bottom: 30px;
        }
        .form-card h2 {
            margin-top: 0;
            color: #c2185b;
            border-bottom: 3px solid #fdd835;
            padding-bottom: 8px;
            display: inline-block;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-size: 13px;
            font-weight: bold;
            color: #ad1457;
            margin-bottom: 6px;
        }
        .form-group input {
            padding: 10px 12px;
            border: 2px solid #ff4081;
            border-radius: 8px;
            background-color: #fff5f8;
            color: #333;
            outline: none;
            font-size: 14px;
        }
        .form-group input:focus {
            border-color: #fbc02d;
            background-color: #ffffff;
        }
        .btn-submit {
            background: linear-gradient(135deg, #ff4081, #fbc02d) !important;
            color: #ffffff !important;
            border: none;
            padding: 12px 28px;
            font-weight: bold;
            font-size: 15px;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 3px 8px rgba(0,0,0,0.15);
            transition: transform 0.1s ease;
        }
        .btn-submit:active {
            transform: scale(0.98);
        }
        .btn-cancel {
            background-color: #e0e0e0;
            color: #333;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            margin-left: 8px;
            display: inline-block;
        }
        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
        }
        .product-card {
            background-color: #ffffff !important;
            border-top: 6px solid #ff4081 !important;
            border-left: 2px solid #ff80ab !important;
            border-right: 2px solid #ff80ab !important;
            border-bottom: 2px solid #ff80ab !important;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .product-title {
            font-size: 18px;
            font-weight: 800;
            color: #880e4f;
            margin: 0 0 8px 0;
        }
        .badge-category {
            display: inline-block;
            background-color: #fff59d !important; /* Yellow Badge */
            color: #f57f17 !important;
            font-size: 12px;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 12px;
            border: 1px solid #fbc02d;
        }
        .product-price {
            font-size: 17px;
            font-weight: 800;
            color: #c2185b;
            margin: 6px 0;
        }
        .product-stock {
            font-size: 13px;
            color: #616161;
            font-weight: 600;
        }
        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            border-top: 2px dashed #ffe0b2;
            padding-top: 14px;
        }
        .btn-edit {
            background-color: #ffeb3b !important; /* Kuning tombol edit */
            color: #f57f17 !important;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            flex: 1;
            border: 1px solid #fbc02d;
        }
        .btn-delete {
            background-color: #ff4081 !important; /* Pink tombol hapus */
            color: #ffffff !important;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <header>
            <h1>Final Project: Product Manager</h1>
            <p>Protected Product Manager Apps (Pink & Yellow Theme)</p>
        </header>

        <!-- Pesan Sukses -->
        <?php if (!empty($success)): ?>
            <div class="alert-success">
                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- Pesan Error -->
        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Form Tambah / Edit -->
        <section class="form-card">
            <h2><?= $editProduct ? 'Edit Product' : 'Add New Products' ?></h2>
            <form action="index.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <?php if ($editProduct): ?>
                    <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Product Name (min 3 characters):</label>
                        <input type="text" name="name" required value="<?= htmlspecialchars($editProduct['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label>Categories:</label>
                        <input type="text" name="category" required value="<?= htmlspecialchars($editProduct['category'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label>Price (Rp):</label>
                        <input type="number" step="0.01" name="price" required value="<?= htmlspecialchars($editProduct['price'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label>Stock:</label>
                        <input type="number" name="stock" required value="<?= htmlspecialchars($editProduct['stock'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" name="action_save" class="btn-submit">
                        <?= $editProduct ? 'Update Product' : 'Save Products' ?>
                    </button>
                    <?php if ($editProduct): ?>
                        <a href="index.php" class="btn-cancel">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <!-- Daftar Produk -->
        <section>
            <h2 style="color: #ad1457; margin-bottom: 16px;">Product List</h2>
            <div class="product-grid">
                <?php if (empty($products)): ?>
                    <p style="font-style: italic; color: #888;">Belum ada data produk.</p>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <div class="product-card">
                            <div>
                                <h3 class="product-title"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <span class="badge-category">Category: <?= htmlspecialchars($p['category'], ENT_QUOTES, 'UTF-8') ?></span>
                                <div class="product-price">Price: IDR <?= number_format($p['price'], 2, ',', '.') ?></div>
                                <div class="product-stock">Stock: <?= (int)$p['stock'] ?></div>
                            </div>
                            <div class="card-actions">
                                <a href="index.php?edit=<?= $p['id'] ?>" class="btn-edit">Edit</a>
                                <form action="index.php" method="POST" style="flex: 1;" onsubmit="return confirm('Yakin hapus produk ini?');">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" name="action_delete" class="btn-delete">Remove</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </div>
</body>
</html>