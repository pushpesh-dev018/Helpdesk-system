<?php
require_once 'includes/db.php'; require_once 'includes/header.php'; require_login();
$uid=current_user_id(); $filter=$_GET['status']??'all';
$where="WHERE t.user_id=$uid";
if($filter!=='all') $where.=" AND t.status='".$conn->real_escape_string($filter)."'";
$tickets=$conn->query("SELECT t.*,u.name requester FROM tickets t JOIN users u ON t.user_id=u.id $where ORDER BY t.created_at DESC");
?>
<script>document.getElementById('topbar-title').textContent='My Tickets';</script>
<div class="page-header">
    <div><div class="page-title">My Tickets</div><div class="page-sub">Track all your submitted support requests</div></div>
    <a href="/Helpdesk/submit_ticket.php" class="btn btn-primary">➕ New Ticket</a>
</div>
<div class="filter-bar">
    <?php foreach(['all'=>'All Tickets','Open'=>'Open','In Progress'=>'In Progress','Resolved'=>'Resolved','Closed'=>'Closed'] as $v=>$l): ?>
    <a href="?status=<?=urlencode($v)?>" class="filter-btn <?=$filter===$v?'active':''?>"><?=$l?></a>
    <?php endforeach; ?>
</div>
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Ticket #</th><th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th>SLA Deadline</th><th>Created</th><th>Action</th></tr></thead>
            <tbody>
            <?php if($tickets->num_rows===0): ?>
                <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">🎫</div><p>No tickets found. <a href="/Helpdesk/submit_ticket.php">Create one!</a></p></div></td></tr>
            <?php endif; ?>
            <?php while($row=$tickets->fetch_assoc()):
                $sla_ok=!$row['sla_deadline']||strtotime($row['sla_deadline'])>time();
            ?>
            <tr>
                <td><strong style="color:var(--primary)"><?=htmlspecialchars($row['ticket_no'])?></strong></td>
                <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($row['title'])?></td>
                <td><span class="chip"><?=$row['category']?></span></td>
                <td><span class="badge badge-<?=strtolower($row['priority'])?>"><?=$row['priority']?></span></td>
                <td><span class="badge badge-status-<?=strtolower(str_replace(' ','-',$row['status']))?>"><?=$row['status']?></span></td>
                <td style="font-size:12px;color:<?=$sla_ok?'var(--success)':'var(--danger)'?>"><?=$row['sla_deadline']?date('d M, H:i',strtotime($row['sla_deadline'])):'—'?></td>
                <td style="font-size:12px;color:var(--muted)"><?=date('d M Y',strtotime($row['created_at']))?></td>
                <td><a href="/Helpdesk/ticket_detail.php?id=<?=$row['id']?>" class="btn btn-secondary btn-sm">View</a></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
