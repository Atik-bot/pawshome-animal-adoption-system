<?php
include("../config/db.php");
if(!isset($_SESSION['user_id'])){ header("Location: /auth/login.php"); exit; }
$uid=(int)$_SESSION['user_id'];
$stmt=mysqli_prepare($conn,"SELECT ar.RequestID,ar.RequestDate,ar.Status,a.Name AnimalName,a.AnimalID,a.Age,a.Gender,p.PhotoURL FROM Adoption_Request ar JOIN Animal a ON ar.AnimalID=a.AnimalID LEFT JOIN Photo p ON a.AnimalID=p.AnimalID WHERE ar.UserID=? ORDER BY ar.RequestDate DESC");
mysqli_stmt_bind_param($stmt,"i",$uid); mysqli_stmt_execute($stmt); $result=mysqli_stmt_get_result($stmt);
$page_title = 'My Requests';
include("../includes/header.php");
?>

<!-- Banner -->
<div class="gradient-hero paw-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center text-3xl border border-white/30">❤️</div>
            <div class="text-white">
                <h1 class="text-2xl font-extrabold">My Adoption Requests</h1>
                <p class="text-white/75 text-sm mt-0.5">Track the status of your applications</p>
            </div>
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
    <?php if(mysqli_num_rows($result)===0): ?>
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 text-center py-20 px-4 animate-fade-up">
        <div class="text-7xl mb-4">🐾</div>
        <h3 class="text-xl font-bold text-gray-700 mb-2">No requests yet</h3>
        <p class="text-gray-400 text-sm mb-6 max-w-xs mx-auto">You haven't submitted any adoption requests. Start by browsing available pets!</p>
        <a href="/index.php" class="gradient-hero text-white px-7 py-3 rounded-full text-sm font-bold shadow-md hover:shadow-lg transition-all inline-block">Browse Pets</a>
    </div>
    <?php else: ?>
    <div class="space-y-3">
    <?php
    $sc=['Pending'=>['bg-yellow-50 border-yellow-200','bg-yellow-400','bg-yellow-100 text-yellow-700','fa-clock'],
         'Accepted'=>['bg-green-50 border-green-200','bg-green-500','bg-green-100 text-green-700','fa-check-circle'],
         'Rejected'=>['bg-red-50 border-red-200','bg-red-400','bg-red-100 text-red-600','fa-times-circle']];
    while($row=mysqli_fetch_assoc($result)):
        [$cardBg,$bar,$badge,$icon]=$sc[$row['Status']]??['bg-white border-gray-200','bg-gray-300','bg-gray-100 text-gray-600','fa-question'];
        $img=!empty($row['PhotoURL'])?'/uploads/'.htmlspecialchars($row['PhotoURL']):'https://placehold.co/100x100/fff7ed/f97316?text='.urlencode($row['AnimalName'][0]);
    ?>
    <div class="bg-white dark:bg-gray-800 border <?=explode(' ',$cardBg)[1]?> rounded-2xl shadow-sm overflow-hidden flex items-center gap-0 card-hover animate-fade-up">
        <!-- Left status bar -->
        <div class="w-1.5 self-stretch <?=$bar?> flex-shrink-0"></div>
        <!-- Pet image -->
        <img src="<?=$img?>" class="w-20 h-20 object-cover flex-shrink-0 m-3 rounded-xl" onerror="this.src='https://placehold.co/100x100/fff7ed/f97316?text=?'">
        <!-- Info -->
        <div class="flex-1 min-w-0 py-3 pr-3">
            <h3 class="font-bold text-gray-800 text-base truncate"><?=htmlspecialchars($row['AnimalName'])?></h3>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-gray-400 mt-0.5">
                <span><i class="fas fa-birthday-cake mr-1 text-brand-300"></i><?=htmlspecialchars($row['Age'])?> yr</span>
                <span><i class="fas fa-venus-mars mr-1 text-blue-300"></i><?=htmlspecialchars($row['Gender'])?></span>
                <span><i class="fas fa-calendar mr-1"></i><?=htmlspecialchars($row['RequestDate'])?></span>
                <span class="text-gray-300">Req #<?=$row['RequestID']?></span>
            </div>
        </div>
        <!-- Status + link -->
        <div class="flex flex-col items-end gap-2 pr-4 flex-shrink-0">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full border <?=$badge?> <?=explode(' ',$cardBg)[1]?>">
                <i class="fas <?=$icon?> text-xs"></i><?=htmlspecialchars($row['Status'])?>
            </span>
            <a href="/user/pet_details.php?id=<?=$row['AnimalID']?>" class="text-xs text-brand-500 hover:underline font-medium">View pet →</a>
        </div>
    </div>
    <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>
<?php include("../includes/footer.php"); ?>