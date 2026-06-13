<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
require_login();
$success=$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $title=trim($_POST['title']??''); $desc=trim($_POST['description']??'');
    $cat=$_POST['category']??''; $prio=$_POST['priority']??'Medium'; $uid=current_user_id();
    $sla_map=['Critical'=>4,'High'=>8,'Medium'=>24,'Low'=>72];
    $sla_time=date('Y-m-d H:i:s',strtotime('+'.$sla_map[$prio].' hours'));
    $last=$conn->query("SELECT MAX(id) AS mid FROM tickets")->fetch_assoc()['mid']??0;
    $tno='TKT-'.str_pad($last+1,4,'0',STR_PAD_LEFT);
    $stmt=$conn->prepare("INSERT INTO tickets(ticket_no,user_id,title,description,category,priority,sla_deadline) VALUES(?,?,?,?,?,?,?)");
    $stmt->bind_param("sisssss",$tno,$uid,$title,$desc,$cat,$prio,$sla_time);
    if($stmt->execute()){
        $nid=$conn->insert_id;
        $note="Ticket created by ".current_user_name();
        $lg=$conn->prepare("INSERT INTO ticket_logs(ticket_id,user_id,note) VALUES(?,?,?)");
        $lg->bind_param("iis",$nid,$uid,$note); $lg->execute();
        $success="Ticket <strong>$tno</strong> submitted! <a href='/Helpdesk/ticket_detail.php?id=$nid'>View it →</a>";
    } else { $error="Submission failed. Try again."; }
}
?>
<script>document.getElementById('topbar-title').textContent='New Ticket';</script>
<div class="page-header">
    <div><div class="page-title">Submit New Ticket</div><div class="page-sub">Describe your issue and our team will get back to you</div></div>
</div>
<?php if($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>
<?php if($error):   ?><div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="card" style="max-width:720px">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Category *</label>
                <div class="input-wrap"><span class="iicon">🏷️</span>
                <select name="category" required>
                    <?php foreach(['Hardware','Software','Network','Access','Email','Security','Other'] as $c): ?><option><?=$c?></option><?php endforeach; ?>
                </select></div>
            </div>
            <div class="form-group">
                <label class="form-label">Priority *</label>
                <div class="input-wrap"><span class="iicon">🚦</span>
                <select name="priority" required>
                    <option>Low</option><option selected>Medium</option><option>High</option><option>Critical</option>
                </select></div>
            </div>
            <div class="form-group full">
                <label class="form-label">Subject *</label>
                <div class="input-wrap"><span class="iicon">📝</span>
                <input type="text" name="title" required placeholder="Brief summary of the issue" maxlength="255"></div>
            </div>
            <div class="form-group full">
                <label class="form-label">Description *</label>
                <textarea name="description" required placeholder="Describe the issue in detail — what happened, error messages, steps to reproduce..."></textarea>
            </div>
            <div class="form-group full">
                <label class="form-label">Attach File (optional, max 5MB)</label>
                <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.txt,.doc,.docx">
                <div style="font-size:11px;color:var(--muted);margin-top:4px">Allowed: images, PDF, Word, text files</div>
            </div>
        </div>
        <div style="display:flex;gap:.75rem;margin-top:.5rem">
            <button type="submit" class="btn btn-primary">🚀 Submit Ticket</button>
            <a href="/Helpdesk/index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<!-- SLA Info -->
<div class="card" style="max-width:720px">
    <div class="card-title" style="margin-bottom:.75rem">⏱️ SLA Policy</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.75rem">
        <?php foreach(['Critical'=>['4 hours','var(--danger)'],'High'=>['8 hours','var(--warning)'],'Medium'=>['24 hours','var(--primary)'],'Low'=>['72 hours','var(--success)']] as $p=>[$t,$c]): ?>
        <div style="background:var(--light);border:1px solid var(--border);border-radius:8px;padding:.85rem;border-left:3px solid <?=$c?>">
            <div style="font-size:12px;font-weight:700;color:<?=$c?>"><?=$p?></div>
            <div style="font-size:18px;font-weight:700;margin:.25rem 0"><?=$t?></div>
            <div style="font-size:11px;color:var(--muted)">Response time</div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
