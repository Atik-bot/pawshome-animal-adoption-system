<?php
include("../config/db.php");
$aid=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if(!$aid){ header("Location:../index.php"); exit; }
$stmt=mysqli_prepare($conn,"SELECT a.*,p.PhotoURL,u.UserName OwnerName FROM Animal a LEFT JOIN Photo p ON a.AnimalID=p.AnimalID LEFT JOIN Users u ON a.UserID=u.UserID WHERE a.AnimalID=? LIMIT 1");
mysqli_stmt_bind_param($stmt,"i",$aid); mysqli_stmt_execute($stmt);
$pet=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if(!$pet){ header("Location:../index.php"); exit; }
$page_title=$pet['Name'];
$stmt2=mysqli_prepare($conn,"SELECT a.AnimalID,a.Name,a.Age,a.Gender,a.Status,p.PhotoURL FROM Animal a LEFT JOIN Photo p ON a.AnimalID=p.AnimalID WHERE a.AnimalID!=? AND a.Status='Available' GROUP BY a.AnimalID LIMIT 4");
mysqli_stmt_bind_param($stmt2,"i",$aid); mysqli_stmt_execute($stmt2); $similar=mysqli_stmt_get_result($stmt2);
$msg=''; $mtype='success';
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_SESSION['user_id'])){
    csrf_verify();
    $uid=(int)$_SESSION['user_id'];
    $chk=mysqli_prepare($conn,"SELECT RequestID FROM Adoption_Request WHERE UserID=? AND AnimalID=?");
    mysqli_stmt_bind_param($chk,"ii",$uid,$aid); mysqli_stmt_execute($chk); mysqli_stmt_store_result($chk);
    if(mysqli_stmt_num_rows($chk)>0){ $msg="You already requested this pet."; $mtype='error'; }
    else{
        $ins=mysqli_prepare($conn,"INSERT INTO Adoption_Request(AnimalID,UserID,RequestDate,Status) VALUES(?,?,CURDATE(),'Pending')");
        mysqli_stmt_bind_param($ins,"ii",$aid,$uid);
        $msg=mysqli_stmt_execute($ins)?"Request sent successfully! 🎉":"Something went wrong.";
    }
}
$img=!empty($pet['PhotoURL'])?'/uploads/'.htmlspecialchars($pet['PhotoURL']):'https://placehold.co/800x500/fff7ed/f97316?text=No+Photo';
$sc=['Available'=>'bg-green-100 text-green-700','Adopted'=>'bg-blue-100 text-blue-700','Pending'=>'bg-yellow-100 text-yellow-700'];
$badge=$sc[$pet['Status']]??'bg-gray-100 text-gray-600';
include("../includes/header.php");
?>

<!-- Banner breadcrumb -->
<div class="gradient-hero paw-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <nav class="text-white/70 text-xs mb-3 flex items-center gap-2">
            <a href="/index.php" class="hover:text-white">Home</a>
            <i class="fas fa-chevron-right text-[10px]"></i>
            <span class="text-white font-medium"><?=htmlspecialchars($pet['Name'])?></span>
        </nav>
        <h1 class="text-3xl font-extrabold text-white"><?=htmlspecialchars($pet['Name'])?></h1>
        <p class="text-white/75 text-sm mt-1">Pet details & adoption info</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <?php if($msg): ?>
    <div class="mb-6 p-4 rounded-2xl text-sm flex items-center gap-2 animate-fade-up <?=$mtype==='success'?'bg-green-50 border border-green-200 text-green-700':'bg-red-50 border border-red-200 text-red-700'?>">
        <i class="fas fa-<?=$mtype==='success'?'check-circle':'exclamation-circle'?> text-lg"></i>
        <?=htmlspecialchars($msg)?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <!-- Left: image + description -->
        <div class="lg:col-span-3 space-y-5">
            <div class="rounded-3xl overflow-hidden shadow-md bg-brand-50">
                <img src="<?=$img?>" class="w-full h-96 object-cover" onerror="this.src='https://placehold.co/800x500/fff7ed/f97316?text=No+Photo'">
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h2 class="font-bold text-gray-800 mb-3 flex items-center gap-2"><i class="fas fa-info-circle text-brand-400"></i>About <?=htmlspecialchars($pet['Name'])?></h2>
                <p class="text-gray-500 text-sm leading-relaxed"><?=nl2br(htmlspecialchars($pet['Description']))?></p>
                <?php if(!empty($pet['OwnerName'])): ?>
                <div class="mt-4 pt-4 border-t border-gray-50 dark:border-gray-700 flex items-center gap-3">
                    <div class="w-9 h-9 gradient-hero text-white rounded-full flex items-center justify-center text-sm font-bold"><?=strtoupper(substr($pet['OwnerName'],0,1))?></div>
                    <div><p class="text-[11px] text-gray-400 dark:text-gray-400">Listed by</p><p class="text-sm font-semibold text-gray-700 dark:text-gray-200"><?=htmlspecialchars($pet['OwnerName'])?></p></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: info + adoption -->
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-start justify-between mb-4">
                    <h1 class="text-3xl font-extrabold text-gray-800 dark:text-gray-100"><?=htmlspecialchars($pet['Name'])?></h1>
                    <span class="text-xs font-bold px-3 py-1.5 rounded-full <?=$badge?>"><?=htmlspecialchars($pet['Status'])?></span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <?php $attrs=[['fa-birthday-cake','Age',($pet['Age']??'-').' yr(s)'],['fa-venus-mars','Gender',$pet['Gender']??'-'],['fa-paw','Type',$pet['Type']??'N/A'],['fa-id-badge','ID','#'.$pet['AnimalID']]];
                    foreach($attrs as [$ic,$lbl,$val]): ?>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3 text-center">
                        <i class="fas <?=$ic?> text-brand-400 text-lg mb-1 block"></i>
                        <p class="text-[11px] text-gray-400 dark:text-gray-400"><?=$lbl?></p>
                        <p class="text-sm font-bold text-gray-700 dark:text-gray-200"><?=htmlspecialchars($val)?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2"><i class="fas fa-heart text-brand-400"></i>Adopt <?=htmlspecialchars($pet['Name'])?></h3>
                <?php if($pet['Status']==='Available'): ?>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <p class="text-sm text-gray-400 mb-4">Submit your request and our team will review it shortly.</p>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit" class="w-full gradient-hero text-white font-bold py-3.5 rounded-xl text-sm shadow-md hover:shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2">
                            <i class="fas fa-heart"></i> Send Adoption Request
                        </button>
                    </form>
                    <?php else: ?>
                    <p class="text-sm text-gray-400 mb-4">Sign in to submit an adoption request.</p>
                    <a href="/auth/login.php" class="w-full gradient-hero text-white font-bold py-3.5 rounded-xl text-sm shadow-md transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-sign-in-alt"></i> Login to Adopt
                    </a>
                    <?php endif; ?>
                <?php else: ?>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 text-center">
                    <i class="fas fa-ban text-gray-200 text-3xl mb-2 block"></i>
                    <p class="text-sm text-gray-400">Currently <strong><?=htmlspecialchars($pet['Status'])?></strong> — not available for new requests.</p>
                </div>
                <?php endif; ?>
                <a href="/index.php" class="flex items-center justify-center gap-2 w-full mt-3 border-2 border-gray-200 text-gray-600 hover:border-brand-400 hover:text-brand-500 font-semibold py-2.5 rounded-xl text-sm transition-all">
                    <i class="fas fa-arrow-left"></i> Back to All Pets
                </a>
            </div>
        </div>
    </div>

    <!-- Similar pets -->
    <?php if(mysqli_num_rows($similar)>0): ?>
    <div class="mt-12">
        <h2 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100 mb-5">You Might Also Like</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        <?php while($s=mysqli_fetch_assoc($similar)):
            $si=!empty($s['PhotoURL'])?'/uploads/'.htmlspecialchars($s['PhotoURL']):'https://placehold.co/300x200/fff7ed/f97316?text=No+Photo';
        ?>
        <a href="/user/pet_details.php?id=<?=$s['AnimalID']?>" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden card-hover group">
            <div class="overflow-hidden h-36 bg-brand-50 dark:bg-gray-700"><img src="<?=$si?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.src='https://placehold.co/300x200/fff7ed/f97316?text=?'"></div>
            <div class="p-3">
                <p class="font-bold text-sm text-gray-800"><?=htmlspecialchars($s['Name'])?></p>
                <p class="text-xs text-gray-400"><?=htmlspecialchars($s['Age'])?> yr • <?=htmlspecialchars($s['Gender'])?></p>
            </div>
        </a>
        <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include("../includes/footer.php"); ?>
