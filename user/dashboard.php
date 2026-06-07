<?php
include("../config/db.php");
if(!isset($_SESSION['user_id'])){ header("Location: /auth/login.php"); exit; }
$uid=(int)$_SESSION['user_id'];
$uname=htmlspecialchars($_SESSION['user_name']);

function dash_count($conn,$sql,$uid){
    $st=mysqli_prepare($conn,$sql);
    mysqli_stmt_bind_param($st,"i",$uid);
    mysqli_stmt_execute($st);
    return (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($st))['c'] ?? 0);
}
$listed   =dash_count($conn,"SELECT COUNT(*) c FROM Animal WHERE UserID=?",$uid);
$reqs     =dash_count($conn,"SELECT COUNT(*) c FROM Adoption_Request WHERE UserID=?",$uid);
$accepted =dash_count($conn,"SELECT COUNT(*) c FROM Adoption_Request WHERE UserID=? AND Status='Accepted'",$uid);
$pending  =dash_count($conn,"SELECT COUNT(*) c FROM Adoption_Request WHERE UserID=? AND Status='Pending'",$uid);

$stmt=mysqli_prepare($conn,"SELECT ar.RequestID,ar.RequestDate,ar.Status,a.Name AnimalName,a.AnimalID,p.PhotoURL FROM Adoption_Request ar JOIN Animal a ON ar.AnimalID=a.AnimalID LEFT JOIN Photo p ON a.AnimalID=p.AnimalID WHERE ar.UserID=? ORDER BY ar.RequestDate DESC LIMIT 5");
mysqli_stmt_bind_param($stmt,"i",$uid); mysqli_stmt_execute($stmt); $recent=mysqli_stmt_get_result($stmt);

$stmt2=mysqli_prepare($conn,"SELECT a.AnimalID,a.Name,a.Status,a.Age,a.Gender,p.PhotoURL FROM Animal a LEFT JOIN Photo p ON a.AnimalID=p.AnimalID WHERE a.UserID=? GROUP BY a.AnimalID ORDER BY a.AnimalID DESC LIMIT 6");
mysqli_stmt_bind_param($stmt2,"i",$uid); mysqli_stmt_execute($stmt2); $pets=mysqli_stmt_get_result($stmt2);
$page_title = 'My Dashboard';
include("../includes/header.php");
?>

<!-- Banner (gradient stays orange in both modes — text explicitly white) -->
<div class="gradient-hero paw-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center text-2xl font-extrabold text-white border border-white/30 shadow-md">
                <?=strtoupper(substr($uname,0,1))?>
            </div>
            <div>
                <p class="text-sm text-white/80 font-medium">Welcome back</p>
                <h1 class="text-2xl font-extrabold text-white"><?=$uname?> 👋</h1>
            </div>
        </div>
        <a href="/user/add_pet.php" class="inline-flex items-center gap-2 bg-white text-brand-500 font-bold px-5 py-2.5 rounded-full text-sm shadow-md hover:shadow-lg transition-all active:scale-95">
            <i class="fas fa-plus-circle"></i> List a Pet
        </a>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Stat cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <?php
        $stats=[
            ['Pets Listed',    $listed,   'fa-paw',       'linear-gradient(to bottom right, #fb923c, #ea580c)'],
            ['Total Requests', $reqs,     'fa-file-alt',  'linear-gradient(to bottom right, #60a5fa, #2563eb)'],
            ['Approved',       $accepted, 'fa-check',     'linear-gradient(to bottom right, #4ade80, #16a34a)'],
            ['Pending',        $pending,  'fa-clock',     'linear-gradient(to bottom right, #facc15, #d97706)'],
        ];
        foreach($stats as [$lbl,$val,$ic,$grad]):
        ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex items-center gap-4 card-hover animate-fade-up">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm" style="background: <?=$grad?>">
                <i class="fas <?=$ic?> text-white text-lg"></i>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-gray-800 dark:text-gray-100"><?=$val?></div>
                <div class="text-xs text-gray-400 dark:text-gray-400 mt-0.5"><?=$lbl?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Quick actions -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center gap-2">
                <i class="fas fa-bolt text-brand-500"></i>
                <h2 class="font-bold text-gray-800 dark:text-gray-100">Quick Actions</h2>
            </div>
            <div class="p-5 grid grid-cols-2 gap-3">
                <?php
                $acts=[
                    ['../user/add_pet.php',     'fa-plus-circle',    'List Pet',    'bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 hover:bg-brand-100 dark:hover:bg-brand-900/40 border border-brand-100 dark:border-brand-900/30'],
                    ['../user/my_requests.php', 'fa-heart',          'My Requests', 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 border border-blue-100 dark:border-blue-900/30'],
                    ['../index.php',            'fa-search',         'Browse Pets', 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/40 border border-green-100 dark:border-green-900/30'],
                    ['../auth/logout.php',      'fa-sign-out-alt',   'Logout',      'bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 border border-red-100 dark:border-red-900/30'],
                ];
                foreach($acts as [$href,$ic,$lbl,$cls]):
                ?>
                <a href="<?=$href?>" class="flex flex-col items-center justify-center gap-2 <?=$cls?> rounded-2xl py-5 text-xs font-semibold transition-all">
                    <i class="fas <?=$ic?> text-2xl"></i><?=$lbl?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent requests -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-2"><i class="fas fa-clock text-brand-500"></i><h2 class="font-bold text-gray-800 dark:text-gray-100">Recent Requests</h2></div>
                <a href="/user/my_requests.php" class="text-xs text-brand-500 font-semibold hover:underline">View all →</a>
            </div>
            <div class="p-5">
                <?php if(mysqli_num_rows($recent)===0): ?>
                <div class="text-center py-10 text-gray-400">
                    <i class="fas fa-inbox text-4xl mb-3 block text-gray-200 dark:text-gray-600"></i>
                    <p class="text-sm">No adoption requests yet.</p>
                    <a href="/index.php" class="mt-3 inline-block text-xs text-brand-500 font-semibold hover:underline">Browse pets to get started →</a>
                </div>
                <?php else: ?>
                <div class="space-y-2">
                <?php while($row=mysqli_fetch_assoc($recent)):
                    $sc=['Pending'=>['bg-yellow-100 text-yellow-700','fa-clock'],'Accepted'=>['bg-green-100 text-green-700','fa-check-circle'],'Rejected'=>['bg-red-100 text-red-600','fa-times-circle']];
                    [$badge,$icon]=$sc[$row['Status']]??['bg-gray-100 text-gray-600','fa-question'];
                    $img=!empty($row['PhotoURL'])?'/uploads/'.htmlspecialchars($row['PhotoURL']):'https://placehold.co/60x60/fff7ed/f97316?text='.urlencode($row['AnimalName'][0]);
                ?>
                <div class="flex items-center gap-3 p-3 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <img src="<?=$img?>" class="w-11 h-11 rounded-xl object-cover flex-shrink-0" onerror="this.src='https://placehold.co/60x60/fff7ed/f97316?text=?'">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate"><?=htmlspecialchars($row['AnimalName'])?></p>
                        <p class="text-xs text-gray-400"><?=htmlspecialchars($row['RequestDate'])?></p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full <?=$badge?> whitespace-nowrap flex items-center gap-1">
                        <i class="fas <?=$icon?> text-xs"></i><?=htmlspecialchars($row['Status'])?>
                    </span>
                </div>
                <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- My listed pets -->
    <?php if(mysqli_num_rows($pets)>0): ?>
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-2"><i class="fas fa-paw text-brand-500"></i><h2 class="font-bold text-gray-800 dark:text-gray-100">My Listed Pets</h2></div>
            <a href="/user/add_pet.php" class="text-xs text-brand-500 font-semibold hover:underline">+ Add more</a>
        </div>
        <div class="p-5 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <?php while($p=mysqli_fetch_assoc($pets)):
            $pi=!empty($p['PhotoURL'])?'/uploads/'.htmlspecialchars($p['PhotoURL']):'https://placehold.co/200x160/fff7ed/f97316?text=No+Photo';
            $ps=['Available'=>'bg-green-100 text-green-700','Adopted'=>'bg-blue-100 text-blue-700','Pending'=>'bg-yellow-100 text-yellow-700'];
            $pb=$ps[$p['Status']]??'bg-gray-100 text-gray-600';
        ?>
        <a href="/user/pet_details.php?id=<?=$p['AnimalID']?>" class="rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 card-hover group bg-white dark:bg-gray-800">
            <div class="overflow-hidden h-28 bg-brand-50 dark:bg-gray-700"><img src="<?=$pi?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.src='https://placehold.co/200x160/fff7ed/f97316?text=?'"></div>
            <div class="p-2.5">
                <p class="text-xs font-bold text-gray-800 dark:text-gray-100 truncate"><?=htmlspecialchars($p['Name'])?></p>
                <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-full mt-1 <?=$pb?>"><?=htmlspecialchars($p['Status'])?></span>
            </div>
        </a>
        <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include("../includes/footer.php"); ?>