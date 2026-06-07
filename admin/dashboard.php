<?php
include("../config/db.php");
if(!isset($_SESSION['admin_id'])){ header("Location: /auth/login.php"); exit; }
$aname=htmlspecialchars($_SESSION['admin_name']??'Admin');

$tusers  =mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM Users"))['c'];
$tanimals=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM Animal"))['c'];
$tavail  =mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM Animal WHERE Status='Available'"))['c'];
$tadopted=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM Animal WHERE Status='Adopted'"))['c'];
$tpend   =mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM Adoption_Request WHERE Status='Pending'"))['c'];
$tacc    =mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM Adoption_Request WHERE Status='Accepted'"))['c'];

$filter=htmlspecialchars($_GET['filter']??'');
$params=[]; $types=''; $where='';
if(in_array($filter,['Pending','Accepted','Rejected'])){ $where="WHERE ar.Status=?"; $params[]=$filter; $types='s'; }

$stmt=mysqli_prepare($conn,"SELECT ar.RequestID,ar.Status,ar.RequestDate,u.UserName,u.Email,a.Name AnimalName,a.AnimalID FROM Adoption_Request ar JOIN Users u ON ar.UserID=u.UserID JOIN Animal a ON ar.AnimalID=a.AnimalID $where ORDER BY ar.RequestDate DESC");
if($params) mysqli_stmt_bind_param($stmt,$types,...$params);
mysqli_stmt_execute($stmt); $result=mysqli_stmt_get_result($stmt);
$page_title = 'Admin Dashboard';
include("../includes/header.php");
?>

<!-- Banner -->
<div class="gradient-hero paw-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center text-2xl font-extrabold text-white border border-white/30">🛡️</div>
            <div class="text-white">
                <p class="text-sm text-white/70">Administrator</p>
                <h1 class="text-2xl font-extrabold"><?=$aname?>'s Dashboard</h1>
            </div>
        </div>
        <a href="/admin/logout.php" class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 border border-white/30 text-white font-semibold px-4 py-2 rounded-full text-sm transition-all backdrop-blur-sm">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Top stat cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <?php
        $stats=[
            ['Total Users',    $tusers,   'fa-users',     'linear-gradient(to bottom right, #60a5fa, #2563eb)'],
            ['Total Pets',     $tanimals, 'fa-paw',       'linear-gradient(to bottom right, #fb923c, #ea580c)'],
            ['Available Now',  $tavail,   'fa-heart',     'linear-gradient(to bottom right, #4ade80, #16a34a)'],
            ['Pets Adopted',   $tadopted, 'fa-home',      'linear-gradient(to bottom right, #c084fc, #9333ea)'],
        ];
        foreach($stats as [$lbl,$val,$ic,$gr]):
        ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex items-center gap-4 card-hover animate-fade-up">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-sm flex-shrink-0" style="background: <?=$gr?>">
                <i class="fas <?=$ic?> text-white text-lg"></i>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-gray-800 dark:text-gray-100"><?=$val?></div>
                <div class="text-xs text-gray-400 dark:text-gray-400"><?=$lbl?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Request mini-stats -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 text-center shadow-sm">
            <div class="text-2xl font-extrabold text-gray-800 dark:text-gray-100"><?=$tusers+$tanimals?></div>
            <div class="text-xs text-gray-400 dark:text-gray-400 mt-0.5">Total Records</div>
        </div>
        <div class="bg-yellow-50 rounded-2xl border border-yellow-100 p-4 text-center shadow-sm">
            <div class="text-2xl font-extrabold text-yellow-600"><?=$tpend?></div>
            <div class="text-xs text-yellow-500 mt-0.5">Awaiting Review</div>
        </div>
        <div class="bg-green-50 rounded-2xl border border-green-100 p-4 text-center shadow-sm">
            <div class="text-2xl font-extrabold text-green-600"><?=$tacc?></div>
            <div class="text-xs text-green-500 mt-0.5">Accepted Total</div>
        </div>
    </div>

    <!-- Requests table -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h2 class="font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <i class="fas fa-list text-brand-500"></i> Adoption Requests
                <?php if($tpend>0): ?><span class="bg-red-500 text-white text-[11px] font-bold px-2 py-0.5 rounded-full"><?=$tpend?></span><?php endif; ?>
            </h2>
            <!-- Filter tabs -->
            <div class="flex gap-2 flex-wrap">
                <?php
                $tabs=[''=> ['All','bg-gray-100 text-gray-600 hover:bg-gray-200'],'Pending'=>['Pending','bg-yellow-100 text-yellow-700 hover:bg-yellow-200'],'Accepted'=>['Accepted','bg-green-100 text-green-700 hover:bg-green-200'],'Rejected'=>['Rejected','bg-red-100 text-red-600 hover:bg-red-200']];
                foreach($tabs as $v=>[$lbl,$cls]):
                    $active=($filter===$v)?'ring-2 ring-brand-400 ring-offset-1 font-extrabold':'';
                ?>
                <a href="?filter=<?=urlencode($v)?>" class="px-3 py-1.5 rounded-full text-xs font-medium transition-all <?=$cls?> <?=$active?>"><?=$lbl?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if(mysqli_num_rows($result)===0): ?>
        <div class="text-center py-16 text-gray-300 dark:text-gray-600">
            <i class="fas fa-inbox text-5xl mb-3 block"></i>
            <p class="text-sm text-gray-400 dark:text-gray-500">No requests found.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900 text-gray-400 dark:text-gray-500 text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold">ID</th>
                        <th class="px-5 py-3 text-left font-semibold">Applicant</th>
                        <th class="px-5 py-3 text-left font-semibold">Pet</th>
                        <th class="px-5 py-3 text-left font-semibold">Date</th>
                        <th class="px-5 py-3 text-left font-semibold">Status</th>
                        <th class="px-5 py-3 text-left font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                <?php while($row=mysqli_fetch_assoc($result)):
                    $sc=['Pending'=>'bg-yellow-100 text-yellow-700','Accepted'=>'bg-green-100 text-green-700','Rejected'=>'bg-red-100 text-red-600'];
                    $bc=$sc[$row['Status']]??'bg-gray-100 text-gray-600';
                ?>
                <tr class="hover:bg-brand-50/30 dark:hover:bg-gray-700 transition-colors">
                    <td class="px-5 py-3.5 text-gray-400 dark:text-gray-500 font-mono text-xs">#<?=$row['RequestID']?></td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 text-white rounded-xl flex items-center justify-center text-xs font-bold flex-shrink-0">
                                <?=strtoupper(substr($row['UserName'],0,1))?>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-700 dark:text-gray-200 text-xs"><?=htmlspecialchars($row['UserName'])?></p>
                                <p class="text-gray-400 text-[11px]"><?=htmlspecialchars($row['Email'])?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <a href="/user/pet_details.php?id=<?=$row['AnimalID']?>" class="font-semibold text-brand-500 hover:underline text-xs">
                            <?=htmlspecialchars($row['AnimalName'])?>
                        </a>
                    </td>
                    <td class="px-5 py-3.5 text-gray-400 dark:text-gray-500 text-xs"><?=htmlspecialchars($row['RequestDate'])?></td>
                    <td class="px-5 py-3.5">
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full <?=$bc?>"><?=htmlspecialchars($row['Status'])?></span>
                    </td>
                    <td class="px-5 py-3.5">
                        <?php if($row['Status']==='Pending'): ?>
                        <div class="flex gap-2">
                            <form method="POST" action="manage_requests.php" onsubmit="return confirm('Accept this request?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="action" value="accept">
                                <input type="hidden" name="id" value="<?=$row['RequestID']?>">
                                <button type="submit" class="inline-flex items-center gap-1 bg-green-500 hover:bg-green-600 text-white text-[11px] font-bold px-3 py-1.5 rounded-lg transition-all shadow-sm">
                                    <i class="fas fa-check"></i>Accept
                                </button>
                            </form>
                            <form method="POST" action="manage_requests.php" onsubmit="return confirm('Reject this request?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="id" value="<?=$row['RequestID']?>">
                                <button type="submit" class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white text-[11px] font-bold px-3 py-1.5 rounded-lg transition-all shadow-sm">
                                    <i class="fas fa-times"></i>Reject
                                </button>
                            </form>
                        </div>
                        <?php else: ?><span class="text-[11px] text-gray-300 dark:text-gray-600 italic">No action</span><?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include("../includes/footer.php"); ?>