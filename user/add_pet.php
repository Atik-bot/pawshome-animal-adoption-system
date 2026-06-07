<?php
include("../config/db.php");
// session managed by config/db.php

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php"); exit;
}
$uid = (int) $_SESSION['user_id'];

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name   = trim($_POST['name']        ?? '');
    $type   = trim($_POST['type']        ?? '');
    $gender = trim($_POST['gender']      ?? '');
    $age    = (int)($_POST['age']        ?? 0);
    $desc   = trim($_POST['description'] ?? '');

    if ($name === '' || $type === '' || $gender === '') {
        $error = 'Please fill in all required fields.';
    } else {
        // Insert animal
        $stmt = mysqli_prepare($conn,
            "INSERT INTO Animal (Name, Type, Gender, Age, Description, Status, UserID)
             VALUES (?, ?, ?, ?, ?, 'Available', ?)");
        mysqli_stmt_bind_param($stmt, "sssisi", $name, $type, $gender, $age, $desc, $uid);

        if (!mysqli_stmt_execute($stmt)) {
            $error = 'Could not save pet. Please try again.';
        } else {
            $new_id = mysqli_insert_id($conn);

            // Handle optional photo upload
            if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $mime    = mime_content_type($_FILES['photo']['tmp_name']);

                if (!in_array($mime, $allowed)) {
                    $error = 'Pet saved, but photo format not allowed (jpeg/png/webp/gif only).';
                } elseif ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                    // InfinityFree upload_max_filesize is 10 MB; we cap at 5 MB to be safe
                    $error = 'Pet saved, but photo is too large (max 5 MB).';
                } else {
                    $ext      = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                    $filename = 'pet_' . bin2hex(random_bytes(8)) . '.' . strtolower($ext);
                    $dest     = __DIR__ . '/../uploads/' . $filename;

                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                        $photo_stmt = mysqli_prepare($conn,
                            "INSERT INTO Photo (AnimalID, PhotoURL) VALUES (?, ?)");
                        mysqli_stmt_bind_param($photo_stmt, "is", $new_id, $filename);
                        mysqli_stmt_execute($photo_stmt);
                    }
                }
            }

            if (!$error) {
                $success = 'Pet listed successfully! 🎉';
            }
        }
    }
}

$page_title = 'List a Pet';
include("../includes/header.php");
?>

<!-- Banner -->
<div class="gradient-hero paw-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center text-2xl border border-white/30">🐾</div>
            <div class="text-white">
                <h1 class="text-2xl font-extrabold">List a Pet for Adoption</h1>
                <p class="text-white/75 text-sm mt-0.5">Help a pet find their forever home</p>
            </div>
        </div>
    </div>
</div>

<div class="max-w-lg mx-auto px-4 py-10">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 animate-fade-up">

        <?php if ($error): ?>
        <div class="rounded-2xl p-4 mb-5 text-sm flex items-center gap-3 bg-red-50 border border-red-200 text-red-700">
            <i class="fas fa-exclamation-circle text-xl flex-shrink-0"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="rounded-2xl p-4 mb-5 text-sm flex items-center gap-3 bg-green-50 border border-green-200 text-green-700">
            <i class="fas fa-check-circle text-xl flex-shrink-0"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
        <div class="flex gap-3">
            <a href="/index.php" class="flex-1 text-center border-2 border-gray-200 text-gray-600 hover:border-brand-400 hover:text-brand-500 font-semibold py-3 rounded-xl text-sm transition-all">Browse All Pets</a>
            <a href="/user/dashboard.php" class="flex-1 text-center gradient-hero text-white font-bold py-3 rounded-xl text-sm shadow-md transition-all">My Dashboard</a>
        </div>
        <?php else: ?>
        <form method="POST" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <!-- Pet Name -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Pet Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" required
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       placeholder="e.g. Buddy"
                       class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition">
            </div>

            <!-- Type & Gender -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Type <span class="text-red-400">*</span></label>
                    <select name="type" required class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition">
                        <option value="">Select...</option>
                        <option value="Dog"  <?= ($_POST['type']??'')==='Dog'  ?'selected':'' ?>>🐶 Dog</option>
                        <option value="Cat"  <?= ($_POST['type']??'')==='Cat'  ?'selected':'' ?>>🐱 Cat</option>
                        <option value="Bird" <?= ($_POST['type']??'')==='Bird' ?'selected':'' ?>>🐦 Bird</option>
                        <option value="Other"<?= ($_POST['type']??'')==='Other'?'selected':'' ?>>🐹 Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Gender <span class="text-red-400">*</span></label>
                    <select name="gender" required class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition">
                        <option value="">Select...</option>
                        <option value="Male"   <?= ($_POST['gender']??'')==='Male'  ?'selected':'' ?>>Male</option>
                        <option value="Female" <?= ($_POST['gender']??'')==='Female'?'selected':'' ?>>Female</option>
                    </select>
                </div>
            </div>

            <!-- Age -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Age (years)</label>
                <input type="number" name="age" min="0" max="30"
                       value="<?= (int)($_POST['age'] ?? 0) ?>"
                       class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Description</label>
                <textarea name="description" rows="4"
                          placeholder="Tell us about this pet's personality, health, history..."
                          class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition resize-none"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- Photo -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Photo (optional, max 5 MB)</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif"
                       class="w-full text-sm text-gray-500 dark:text-gray-400
                              file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0
                              file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-600
                              hover:file:bg-brand-100 transition">
            </div>

            <button type="submit"
                    class="w-full gradient-hero text-white font-bold py-3 rounded-xl text-sm shadow-md hover:shadow-lg transition-all active:scale-95">
                <i class="fas fa-plus-circle mr-2"></i>List This Pet
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
