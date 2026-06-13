<?php
require_once 'includes/db.php'; require_once 'includes/header.php'; require_login();
$id=intval($_GET['id']??0); $uid=current_user_id();
$stmt=$conn->prepare("SELECT t.*,u.name requester,u.email req_email,a.name agent_name FROM tickets t JOIN users u ON t.user_id=u.id LEFT JOIN users a ON t.assigned_to=a.id WHERE t.id=?");
$stmt->bind_param("i",$id); $stmt->execute();
$ticket=$stmt->get_result()->fetch_assoc();
if(!$ticket||(current_role()!=='admin'&&$ticket['user_id']!=$uid)){echo "<div class='alert alert-error'>Access denied.</div>";require_once 'includes/footer.php';exit();}
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['note'])){
    $note=trim($_POST['note']);
    if($note){$ins=$conn->prepare("INSERT INTO ticket_logs(ticket_id,user_id,note) VALUES(?,?,?)");$ins->bind_param("iis",$id,$uid,$note);$ins->execute();}
    header("Location: ticket_detail.php?id=$id");exit();
}
$logs=$conn->query("SELECT l.*,u.name FROM ticket_logs l JOIN users u ON l.user_id=u.id WHERE l.ticket_id=$id ORDER BY l.created_at ASC");
$attachments=$conn->query("SELECT * FROM ticket_attachments WHERE ticket_id=$id ORDER BY created_at DESC");
$canned=current_role()==='admin'?$conn->query("SELECT * FROM canned_responses ORDER BY title"):null;
$rating=null;
if(in_array($ticket['status'],['Resolved','Closed'])){$r=$conn->prepare("SELECT rating FROM ticket_ratings WHERE ticket_id=? AND user_id=?");$r->bind_param("ii",$id,$uid);$r->execute();$rating=$r->get_result()->fetch_assoc();}
$sla_ok=!$ticket['sla_deadline']||strtotime($ticket['sla_deadline'])>time();
$pct=100;
if($ticket['sla_deadline']){$total=strtotime($ticket['sla_deadline'])-strtotime($ticket['created_at']);$elapsed=time()-strtotime($ticket['created_at']);$pct=max(0,min(100,round((1-$elapsed/$total)*100)));}
?>
<script>document.getElementById('topbar-title').textContent='<?=htmlspecialchars($ticket['ticket_no'],ENT_QUOTES)?>';</script>
<div class="page-header">
    <div>
        <div class="page-title"><?=htmlspecialchars($ticket['ticket_no'])?> <span style="font-weight:400;font-size:16px;color:var(--muted)">— <?=htmlspecialchars($ticket['title'])?></span></div>
        <div style="display:flex;gap:.5rem;margin-top:.4rem;flex-wrap:wrap">
            <span class="badge badge-<?=strtolower($ticket['priority'])?>"><?=$ticket['priority']?></span>
            <span class="badge badge-status-<?=strtolower(str_replace(' ','-',$ticket['status']))?>"><?=$ticket['status']?></span>
            <span class="chip"><?=$ticket['category']?></span>
        </div>
    </div>
    <?php if(current_role()==='admin'): ?>
    <div style="display:flex;gap:.5rem">
        <a href="/Helpdesk/admin/update_ticket.php?id=<?=$id?>" class="btn btn-primary btn-sm">✏️ Edit</a>
    </div>
    <?php endif; ?>
</div>

<div class="detail-grid">
<div>
    <!-- Description -->
    <div class="card">
        <div class="card-title" style="margin-bottom:.75rem">📄 Description</div>
        <p style="color:#374151;line-height:1.8"><?=nl2br(htmlspecialchars($ticket['description']))?></p>
    </div>

    <!-- Attachments -->
    <?php if($attachments&&$attachments->num_rows>0): ?>
    <div class="card">
        <div class="card-title" style="margin-bottom:.75rem">📎 Attachments</div>
        <?php while($att=$attachments->fetch_assoc()): $isImg=preg_match('/\.(jpg|jpeg|png|gif)$/i',$att['original_name']); ?>
        <div style="display:flex;align-items:center;gap:10px;padding:.6rem 0;border-bottom:1px solid var(--border)">
            <span style="font-size:22px"><?=$isImg?'🖼️':'📄'?></span>
            <div>
                <a href="/Helpdesk/uploads/<?=htmlspecialchars($att['filename'])?>" target="_blank" style="color:var(--primary);font-weight:500;font-size:13px"><?=htmlspecialchars($att['original_name'])?></a>
                <div style="font-size:11px;color:var(--muted)"><?=date('d M Y',strtotime($att['created_at']))?></div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <!-- Activity Log -->
    <div class="card">
        <div class="card-title" style="margin-bottom:.85rem">💬 Activity Log</div>
        <?php if($logs->num_rows===0): ?><p style="color:var(--muted);font-size:13px">No activity yet.</p><?php endif; ?>
        <?php while($log=$logs->fetch_assoc()): ?>
        <div class="log-entry">
            <div><span class="log-author"><?=htmlspecialchars($log['name'])?></span><span class="log-time"><?=date('d M Y, H:i',strtotime($log['created_at']))?></span></div>
            <div class="log-body"><?=nl2br(htmlspecialchars($log['note']))?></div>
        </div>
        <?php endwhile; ?>

        <form method="POST" enctype="multipart/form-data" style="margin-top:1rem">
            <?php if($canned&&$canned->num_rows>0): ?>
            <div class="form-group">
                <label class="form-label">Quick Reply (Canned)</label>
                <select id="canned-sel" onchange="document.getElementById('note-box').value=this.value;this.selectedIndex=0">
                    <option value="">— Select preset reply —</option>
                    <?php while($c=$canned->fetch_assoc()): ?><option value="<?=htmlspecialchars($c['body'])?>"><?=htmlspecialchars($c['title'])?></option><?php endwhile; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label class="form-label">Add Note / Reply</label>
                <textarea name="note" id="note-box" placeholder="Type your update or reply..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Attach File (optional)</label>
                <input type="file" name="attach" accept=".jpg,.jpeg,.png,.gif,.pdf,.txt,.doc,.docx">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">💬 Add Note</button>
        </form>
    </div>
</div>

<!-- Right Panel -->
<div>
    <!-- Ticket Info -->
    <div class="card">
        <div class="card-title" style="margin-bottom:.85rem">ℹ️ Ticket Info</div>
        <table class="info-table">
            <tr><td>Status</td><td><span class="badge badge-status-<?=strtolower(str_replace(' ','-',$ticket['status']))?>"><?=$ticket['status']?></span></td></tr>
            <tr><td>Priority</td><td><span class="badge badge-<?=strtolower($ticket['priority'])?>"><?=$ticket['priority']?></span></td></tr>
            <tr><td>Category</td><td><?=$ticket['category']?></td></tr>
            <tr><td>Requester</td><td><?=htmlspecialchars($ticket['requester'])?></td></tr>
            <tr><td>Assigned To</td><td><?=htmlspecialchars($ticket['agent_name']??'Unassigned')?></td></tr>
            <tr><td>Created</td><td style="font-size:12px"><?=date('d M Y, H:i',strtotime($ticket['created_at']))?></td></tr>
            <tr><td>SLA Deadline</td><td style="color:<?=$sla_ok?'var(--success)':'var(--danger)'?>;font-size:12px;font-weight:600"><?=$ticket['sla_deadline']?date('d M Y, H:i',strtotime($ticket['sla_deadline'])):'—'?></td></tr>
        </table>
        <!-- SLA Bar -->
        <div style="margin-top:.75rem">
            <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--muted);margin-bottom:4px">
                <span>SLA Remaining</span><span><?=$pct?>%</span>
            </div>
            <div class="sla-bar">
                <div class="sla-fill" style="width:<?=$pct?>%;background:<?=$pct<30?'var(--danger)':$pct<60?'var(--warning)':'var(--success)')?>"></div>
            </div>
        </div>
    </div>

    <!-- Rating -->
    <?php if(in_array($ticket['status'],['Resolved','Closed'])&&$ticket['user_id']==$uid): ?>
    <div class="card" style="text-align:center">
        <div class="card-title" style="margin-bottom:.5rem">⭐ Rate Experience</div>
        <?php if($rating): ?>
            <div style="font-size:28px"><?=str_repeat('⭐',$rating['rating'])?></div>
            <p style="font-size:12px;color:var(--muted);margin-top:.4rem">You rated <?=$rating['rating']?>/5</p>
        <?php else: ?>
            <p style="font-size:13px;color:var(--muted);margin-bottom:.75rem">How was your support experience?</p>
            <a href="/Helpdesk/rate_ticket.php?id=<?=$id?>" class="btn btn-primary w-full">⭐ Rate Now</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
</div>
<?php require_once 'includes/footer.php'; ?>
