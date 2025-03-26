<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $file_path = null;
    $file_type = null;

    // Handle file upload
    if (isset($_FILES['notice_file']) && $_FILES['notice_file']['error'] === 0) {
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        $file_info = pathinfo($_FILES['notice_file']['name']);
        $file_extension = strtolower($file_info['extension']);

        if (in_array($file_extension, $allowed_types)) {
            $upload_dir = '../uploads/notices/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = uniqid() . '_' . time() . '.' . $file_extension;
            $file_path = 'uploads/notices/' . $file_name;

            if (move_uploaded_file($_FILES['notice_file']['tmp_name'], '../' . $file_path)) {
                $file_type = $file_extension;
            }
        }
    }

    $query = "INSERT INTO notices (title, content, file_path, file_type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $title, $content, $file_path, $file_type);
    
    if ($stmt->execute()) {
        header('Location: admin_dashboard.php?success=notice_added');
    } else {
        header('Location: admin_dashboard.php?error=notice_failed');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Notice - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-6">Add New Notice</h2>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                        Notice Title
                    </label>
                    <input type="text" id="title" name="title" required
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="content">
                        Notice Content
                    </label>
                    <textarea id="content" name="content" rows="4"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="notice_file">
                        Attachment (PDF/JPG/PNG)
                    </label>
                    <input type="file" id="notice_file" name="notice_file" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Add Notice
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 