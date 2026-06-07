<?php
include("config/db.php");
include("includes/header.php");

// ── Search / Filter inputs (sanitized) ──────────────────────────────────────
$search   = trim(htmlspecialchars($_GET['search']   ?? ''));
$type     = trim(htmlspecialchars($_GET['type']     ?? ''));
$gender   = trim(htmlspecialchars($_GET['gender']   ?? ''));
$status   = trim(htmlspecialchars($_GET['status']   ?? 'Available'));

// ── Build safe parameterised query ─────────────────────────────────────────
$conditions = ["1=1"];
$params     = [];
$types      = "";

if ($search !== '') {
    $conditions[] = "(a.Name LIKE ? OR a.Description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types   .= "ss";
}
if ($type !== '') {
    $conditions[] = "a.Type = ?";
    $params[] = $type;
    $types   .= "s";
}
if ($gender !== '') {
    $conditions[] = "a.Gender = ?";
    $params[] = $gender;
    $types   .= "s";
}
if ($status !== '') {
    $conditions[] = "a.Status = ?";
    $params[] = $status;
    $types   .= "s";
}

$where = implode(" AND ", $conditions);

// ── Pagination ───────────────────────────────────────────────────────────
$page   = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1);
$per    = 12;
$offset = ($page - 1) * $per;

$sql = "SELECT a.*, p.PhotoURL
        FROM Animal a
        LEFT JOIN Photo p ON a.AnimalID = p.AnimalID
        WHERE $where
        GROUP BY a.AnimalID
        ORDER BY a.AnimalID DESC
        LIMIT ?, 12";

// LIMIT placeholder is the last one in the query, so bind $offset last.
$params[] = $offset;
$types   .= "i";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// ── Stats for hero section ───────────────────────────────────────────────────
$total_available = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM Animal WHERE Status='Available'"))['c'];
$total_adopted   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM Animal WHERE Status='Adopted'"))['c'];
$total_animals   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM Animal"))['c'];
?>

<!-- ===== HERO SECTION ===== -->
<section class="gradient-hero text-white py-20 px-4">
    <div class="max-w-4xl mx-auto text-center animate-fade-up">
        <div class="text-6xl mb-4">🐾</div>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-4 leading-tight drop-shadow">
            Find Your New Best Friend
        </h1>
        <p class="text-lg md:text-xl opacity-90 mb-8 max-w-2xl mx-auto">
            Give a loving animal the home they deserve. Browse pets available for adoption near you.
        </p>
        <a href="#browse"
           class="inline-block bg-white text-orange-500 font-bold px-8 py-3 rounded-full text-lg shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200">
            Browse Pets <i class="fas fa-arrow-down ml-2"></i>
        </a>
    </div>
</section>

<!-- ===== STATS BAR ===== -->
<section class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 shadow-sm">
    <div class="max-w-5xl mx-auto px-4 py-6">
        <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-700 text-center">
            <div class="px-4">
                <div class="text-3xl font-extrabold text-orange-500"><?= $total_animals ?>+</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pets Listed</div>
            </div>
            <div class="px-4">
                <div class="text-3xl font-extrabold text-green-500"><?= $total_available ?></div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Available Now</div>
            </div>
            <div class="px-4">
                <div class="text-3xl font-extrabold text-blue-500"><?= $total_adopted ?>+</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Happy Adoptions</div>
            </div>
        </div>
    </div>
</section>

<!-- ===== SEARCH & FILTER ===== -->
<section id="browse" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <form method="GET" action="" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <!-- Search -->
            <div class="relative sm:col-span-2 md:col-span-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Search pets..."
                       class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition">
            </div>
            <!-- Type filter -->
            <div class="relative">
                <i class="fas fa-paw absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <select name="type"
                        class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition appearance-none">
                    <option value="">All Types</option>
                    <option value="Dog"  <?= $type==='Dog'   ? 'selected':'' ?>>🐶 Dog</option>
                    <option value="Cat"  <?= $type==='Cat'   ? 'selected':'' ?>>🐱 Cat</option>
                    <option value="Bird" <?= $type==='Bird'  ? 'selected':'' ?>>🐦 Bird</option>
                    <option value="Other"<?= $type==='Other' ? 'selected':'' ?>>🐹 Other</option>
                </select>
            </div>
            <!-- Gender filter -->
            <div class="relative">
                <i class="fas fa-venus-mars absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <select name="gender"
                        class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition appearance-none">
                    <option value="">Any Gender</option>
                    <option value="Male"   <?= $gender==='Male'   ? 'selected':'' ?>>Male</option>
                    <option value="Female" <?= $gender==='Female' ? 'selected':'' ?>>Female</option>
                </select>
            </div>
            <!-- Status + Submit -->
            <div class="flex gap-2">
                <select name="status"
                        class="flex-1 px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 transition appearance-none">
                    <option value=""        <?= $status===''          ? 'selected':'' ?>>All Status</option>
                    <option value="Available" <?= $status==='Available' ? 'selected':'' ?>>Available</option>
                    <option value="Adopted"   <?= $status==='Adopted'   ? 'selected':'' ?>>Adopted</option>
                    <option value="Pending"   <?= $status==='Pending'   ? 'selected':'' ?>>Pending</option>
                </select>
                <button type="submit"
                        class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-all whitespace-nowrap shadow-sm">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <?php if ($search || $type || $gender || ($status !== 'Available' && $status !== '')): ?>
        <div class="mt-3 flex items-center gap-2">
            <span class="text-xs text-gray-500 dark:text-gray-400">Active filters:</span>
            <?php if ($search): ?>
                <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full">"<?= htmlspecialchars($search) ?>"</span>
            <?php endif; ?>
            <?php if ($type): ?>
                <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full"><?= htmlspecialchars($type) ?></span>
            <?php endif; ?>
            <a href="/index.php" class="text-xs text-red-400 hover:text-red-600 ml-1"><i class="fas fa-times"></i> Clear</a>
        </div>
        <?php endif; ?>
    </form>
</section>

<!-- ===== PET CARDS GRID ===== -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            <?php if ($search || $type || $gender): ?>
                Search Results
            <?php else: ?>
                <?= $status ?: 'All' ?> Pets
            <?php endif; ?>
        </h2>
        <span class="text-sm text-gray-400 dark:text-gray-500"><?= mysqli_num_rows($result) ?> found</span>
    </div>

    <?php if (mysqli_num_rows($result) === 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm text-center py-20 px-4">
            <div class="text-7xl mb-4 animate-pulse">🐾</div>
            <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-300 mb-2">No pets found</h3>
            <p class="text-gray-400 dark:text-gray-500 text-sm mb-6">Try adjusting your search or filters.</p>
            <a href="/index.php" class="bg-orange-500 text-white px-6 py-2 rounded-full text-sm font-semibold hover:bg-orange-600 transition">
                View All Pets
            </a>
        </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <?php
            $img_src = (!empty($row['PhotoURL']))
                ? '/uploads/' . htmlspecialchars($row['PhotoURL'])
                : 'https://placehold.co/400x300/fff3e0/f97316?text=No+Photo';

            $status_colors = [
                'Available' => 'bg-green-100 text-green-700',
                'Adopted'   => 'bg-blue-100 text-blue-700',
                'Pending'   => 'bg-yellow-100 text-yellow-700',
                'Reserved'  => 'bg-purple-100 text-purple-700',
            ];
            $badge = $status_colors[$row['Status']] ?? 'bg-gray-100 text-gray-700';
        ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden card-hover animate-fade-up">
            <!-- Pet image -->
            <div class="relative h-48 bg-orange-50 dark:bg-gray-700 overflow-hidden">
                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($row['Name']) ?>"
                     class="w-full h-full object-cover" loading="lazy"
                     onerror="this.src='https://placehold.co/400x300/fff3e0/f97316?text=No+Photo'">
                <!-- Status badge -->
                <span class="absolute top-3 right-3 text-xs font-semibold px-2.5 py-1 rounded-full <?= $badge ?>">
                    <?= htmlspecialchars($row['Status']) ?>
                </span>
                <?php if (!empty($row['Type'])): ?>
                <span class="absolute top-3 left-3 text-xs font-medium bg-white/90 text-gray-600 px-2 py-1 rounded-full shadow-sm">
                    <?php
                        $type_icons = ['Dog'=>'🐶','Cat'=>'🐱','Bird'=>'🐦','Other'=>'🐾'];
                        echo ($type_icons[$row['Type']] ?? '🐾') . ' ' . htmlspecialchars($row['Type']);
                    ?>
                </span>
                <?php endif; ?>
            </div>

            <!-- Card body -->
            <div class="p-4">
                <h3 class="font-bold text-lg text-gray-800 dark:text-gray-100 mb-1"><?= htmlspecialchars($row['Name']) ?></h3>
                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mb-2">
                    <span><i class="fas fa-birthday-cake mr-1 text-orange-300"></i><?= htmlspecialchars($row['Age']) ?> yr old</span>
                    <span><i class="fas fa-<?= strtolower($row['Gender'] ?? '') === 'female' ? 'venus' : 'mars' ?> mr-1 text-blue-300"></i><?= htmlspecialchars($row['Gender']) ?></span>
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-500 leading-relaxed line-clamp-2 mb-4">
                    <?= htmlspecialchars($row['Description']) ?>
                </p>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <a href="/user/pet_details.php?id=<?= $row['AnimalID'] ?>"
                       class="flex-1 text-center bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-semibold py-2 rounded-xl transition-all border border-gray-200 dark:border-gray-600">
                       <i class="fas fa-eye mr-1"></i> Details
                    </a>
                    <?php if ($row['Status'] === 'Available'): ?>
                    <a href="/user/adopt.php?id=<?= $row['AnimalID'] ?>"
                       class="flex-1 text-center bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold py-2 rounded-xl transition-all shadow-sm">
                       <i class="fas fa-heart mr-1"></i> Adopt
                    </a>
                    <?php else: ?>
                    <span class="flex-1 text-center bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 text-xs font-semibold py-2 rounded-xl cursor-not-allowed">
                       Not Available
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <?php
    // ── Prev / Next pagination (filters preserved) ──────────────────────────
    $rows_on_page = mysqli_num_rows($result);
    $base_params  = array_filter([
        'search' => $search,
        'type'   => $type,
        'gender' => $gender,
        'status' => $status,
    ], fn($v) => $v !== '');
    $has_prev = $page > 1;
    $has_next = $rows_on_page === $per;
    if ($has_prev || $has_next):
    ?>
    <div class="flex items-center justify-center gap-3 mt-10">
        <?php if ($has_prev): ?>
        <a href="?<?= htmlspecialchars(http_build_query($base_params + ['page' => $page - 1])) ?>"
           class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-brand-400 hover:text-brand-500 text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-sm">
            <i class="fas fa-arrow-left"></i> Previous
        </a>
        <?php endif; ?>
        <span class="text-sm text-gray-400 dark:text-gray-500 px-2">Page <?= $page ?></span>
        <?php if ($has_next): ?>
        <a href="?<?= htmlspecialchars(http_build_query($base_params + ['page' => $page + 1])) ?>"
           class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-brand-400 hover:text-brand-500 text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-sm">
            Next <i class="fas fa-arrow-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section class="bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-extrabold text-gray-800 dark:text-gray-100 mb-2">How Adoption Works</h2>
        <p class="text-gray-400 dark:text-gray-500 mb-10">Simple steps to give a pet a loving home</p>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <?php
            $steps = [
                ['icon'=>'🔍','title'=>'Browse Pets','desc'=>'Search available animals filtered by type, gender and age.'],
                ['icon'=>'📝','title'=>'Apply','desc'=>'Submit your adoption request in one click.'],
                ['icon'=>'✅','title'=>'Get Approved','desc'=>'Our admin reviews and approves your request.'],
                ['icon'=>'🏠','title'=>'Welcome Home','desc'=>'Pick up your new companion and start your journey.'],
            ];
            foreach ($steps as $i => $step):
            ?>
            <div class="flex flex-col items-center">
                <div class="text-4xl mb-3"><?= $step['icon'] ?></div>
                <div class="w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center text-sm font-bold mb-3"><?= $i+1 ?></div>
                <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-1"><?= $step['title'] ?></h3>
                <p class="text-sm text-gray-400 dark:text-gray-500 leading-relaxed"><?= $step['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include("includes/footer.php"); ?>