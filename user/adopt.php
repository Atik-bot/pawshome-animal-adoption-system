<?php
include("../config/db.php");
if(!isset($_SESSION['user_id'])){ header("Location: /auth/login.php"); exit; }
$uid=(int)$_SESSION['user_id'];
$aid=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if(!$aid){ header("Location:../index.php"); exit; }

$stmt=mysqli_prepare($conn,"SELECT a.*,p.PhotoURL FROM Animal a LEFT JOIN Photo p ON a.AnimalID=p.AnimalID WHERE a.AnimalID=? LIMIT 1");
mysqli_stmt_bind_param($stmt,"i",$aid); mysqli_stmt_execute($stmt);
$pet=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if(!$pet||$pet['Status']!=='Available'){ header("Location:../index.php"); exit; }

$msg=''; $type='success';
if($_SERVER["REQUEST_METHOD"]==="POST"){
    csrf_verify();
    $chk=mysqli_prepare($conn,"SELECT RequestID FROM Adoption_Request WHERE UserID=? AND AnimalID=?");
    mysqli_stmt_bind_param($chk,"ii",$uid,$aid); mysqli_stmt_execute($chk); mysqli_stmt_store_result($chk);
    if(mysqli_stmt_num_rows($chk)>0){ $msg="You already submitted a request for this pet."; $type='error'; }
    else{
        $ins=mysqli_prepare($conn,"INSERT INTO Adoption_Request(AnimalID,UserID,RequestDate,Status) VALUES(?,?,CURDATE(),'Pending')");
        mysqli_stmt_bind_param($ins,"ii",$aid,$uid);
        if(mysqli_stmt_execute($ins)){
            $msg="Adoption request sent! We'll review it shortly. 🎉";
        } else {
            $msg="Something went wrong."; $type='error';
        }
    }
}
$img=!empty($pet['PhotoURL'])?'/uploads/'.htmlspecialchars($pet['PhotoURL']):'https://placehold.co/600x400/fff7ed/f97316?text=No+Photo';
include("../includes/header.php");
?>

<!-- Banner -->
<div class="gradient-hero paw-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <nav class="text-white/70 text-xs mb-3 flex items-center gap-2">
            <a href="/index.php" class="hover:text-white transition-colors">Home</a>
            <i class="fas fa-chevron-right text-[10px]"></i>
            <span class="text-white font-medium">Adopt <?=htmlspecialchars($pet['Name'])?></span>
        </nav>
        <h1 class="text-3xl font-extrabold text-white">Adopt <?=htmlspecialchars($pet['Name'])?> 🐾</h1>
        <p class="text-white/75 text-sm mt-1">Submit your application and we'll be in touch soon!</p>
    </div>
</div>

<div class="max-w-xl mx-auto px-4 py-10">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden animate-fade-up">
        <img src="<?=$img?>" class="w-full h-56 object-cover" onerror="this.src='https://placehold.co/600x400/fff7ed/f97316?text=No+Photo'">
        <div class="p-7">
            <div class="flex items-start justify-between mb-4">
                <h2 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100"><?=htmlspecialchars($pet['Name'])?></h2>
                <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1.5 rounded-full">Available</span>
            </div>
            <div class="flex gap-4 text-sm text-gray-500 mb-5">
                <span class="flex items-center gap-1.5"><i class="fas fa-birthday-cake text-brand-300 text-xs"></i><?=htmlspecialchars($pet['Age'])?> yr old</span>
                <span class="flex items-center gap-1.5"><i class="fas fa-venus-mars text-blue-300 text-xs"></i><?=htmlspecialchars($pet['Gender'])?></span>
                <?php if(!empty($pet['Type'])): ?><span class="flex items-center gap-1.5"><i class="fas fa-paw text-brand-300 text-xs"></i><?=htmlspecialchars($pet['Type'])?></span><?php endif; ?>
            </div>

            <?php if($msg): ?>
            <div class="rounded-2xl p-4 mb-5 text-sm flex items-center gap-3 <?=$type==='success'?'bg-green-50 border border-green-200 text-green-700':'bg-red-50 border border-red-200 text-red-700'?>">
                <i class="fas fa-<?=$type==='success'?'check-circle':'exclamation-circle'?> text-xl flex-shrink-0"></i>
                <span><?=htmlspecialchars($msg)?></span>
            </div>
            <?php endif; ?>

            <?php if($type!=='success'||!$msg): ?>
            <div class="bg-brand-50 dark:bg-brand-900/20 border border-brand-100 dark:border-brand-900/30 rounded-2xl p-4 mb-5 text-sm text-brand-700 flex items-start gap-2.5">
                <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
                <span>Your request will be reviewed by our admin team. We'll update the status on your dashboard.</span>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button type="submit" class="w-full gradient-hero text-white font-bold py-3.5 rounded-xl text-sm shadow-md hover:shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2">
                    <i class="fas fa-heart"></i> Confirm Adoption Request
                </button>
            </form>
            <a href="/index.php" class="flex items-center justify-center gap-2 w-full mt-3 border-2 border-gray-200 text-gray-600 hover:border-brand-400 hover:text-brand-500 font-semibold py-3 rounded-xl text-sm transition-all">
                <i class="fas fa-arrow-left"></i> Cancel
            </a>
            <?php else: ?>
            <div class="grid grid-cols-2 gap-3 mt-2">
                <a href="/user/my_requests.php" class="text-center gradient-hero text-white font-bold py-3 rounded-xl text-sm shadow-md transition-all">My Requests</a>
                <a href="/index.php" class="text-center border-2 border-gray-200 text-gray-600 hover:border-brand-400 hover:text-brand-500 font-semibold py-3 rounded-xl text-sm transition-all">Browse More</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include("../includes/footer.php"); ?>