<?php
/**
 * Sigma SMS A2P — Dashboard
 */
require_once __DIR__ . '/functions.php';
requireLogin();
$pageTitle = 'Dashboard';
$user = getCurrentUser();
$role = $user['role'];
include __DIR__ . '/includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
  <div>
    <h2 class="animate-in"><i class="ri-dashboard-line me-2"></i>Dashboard</h2>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb"><li class="breadcrumb-item active">Dashboard</li></ol>
    </nav>
  </div>
  <?php if (in_array($role, ['admin', 'manager'])): ?>
  <button class="btn btn-primary btn-fetch" id="fetchBtn" onclick="triggerFetch()">
    <i class="ri-refresh-line me-1"></i> Fetch OTPs Now
  </button>
  <?php endif; ?>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4" id="statsCards">
  <div class="col-6 col-xl-2 stat-animate" style="--delay:.05s">
    <div class="stat-card bg-stat-1">
      <div>
        <div class="stat-val" id="s-today-sms">–</div>
        <div class="stat-label">Today SMS</div>
      </div>
      <div class="stat-icon"><i class="ri-message-2-line"></i></div>
    </div>
  </div>
  <div class="col-6 col-xl-2 stat-animate" style="--delay:.1s">
    <div class="stat-card bg-stat-2">
      <div>
        <div class="stat-val" id="s-week-sms">–</div>
        <div class="stat-label">Week SMS</div>
      </div>
      <div class="stat-icon"><i class="ri-message-3-line"></i></div>
    </div>
  </div>
  <div class="col-6 col-xl-2 stat-animate" style="--delay:.15s">
    <div class="stat-card bg-stat-3">
      <div>
        <div class="stat-val" id="s-today-profit">–</div>
        <div class="stat-label">Today Profit</div>
      </div>
      <div class="stat-icon"><i class="ri-money-dollar-circle-line"></i></div>
    </div>
  </div>
  <div class="col-6 col-xl-2 stat-animate" style="--delay:.2s">
    <div class="stat-card bg-stat-4">
      <div>
        <div class="stat-val" id="s-week-profit">–</div>
        <div class="stat-label">Week Profit</div>
      </div>
      <div class="stat-icon"><i class="ri-funds-line"></i></div>
    </div>
  </div>
  <div class="col-6 col-xl-2 stat-animate" style="--delay:.25s">
    <div class="stat-card bg-stat-5">
      <div>
        <div class="stat-val" id="s-total-numbers">–</div>
        <div class="stat-label">Numbers</div>
      </div>
      <div class="stat-icon"><i class="ri-sim-card-line"></i></div>
    </div>
  </div>
  <div class="col-6 col-xl-2 stat-animate" style="--delay:.3s">
    <div class="stat-card bg-stat-6">
      <div>
        <div class="stat-val" id="s-total-users">–</div>
        <div class="stat-label"><?= ($role === 'reseller') ? 'Clients' : 'Users' ?></div>
      </div>
      <div class="stat-icon"><i class="ri-team-line"></i></div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
  <div class="col-lg-8 chart-animate" style="--delay:.35s">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="ri-line-chart-line me-1 text-primary"></i>SMS Activity — Last 7 Days</span>
        <span class="badge bg-primary">Area Chart</span>
      </div>
      <div class="card-body">
        <div id="chartSms" style="min-height:260px;"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-4 chart-animate" style="--delay:.4s">
    <div class="card h-100">
      <div class="card-header">
        <i class="ri-pie-chart-line me-1 text-primary"></i>Top 5 Services
      </div>
      <div class="card-body">
        <div id="chartServices" style="min-height:260px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Recent OTPs -->
<div class="card chart-animate" style="--delay:.45s">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="ri-time-line me-1 text-primary"></i>Recent OTPs</span>
    <a href="<?= APP_URL ?>/sms_reports.php" class="btn btn-sm btn-outline-primary">
      <i class="ri-arrow-right-line me-1"></i>View All
    </a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" id="recentOtpsTable">
        <thead>
          <tr>
            <th>Received At</th>
            <th>Number</th>
            <th>Service</th>
            <th>Country</th>
            <th>OTP</th>
            <th>Message</th>
            <?php if (in_array($role, ['admin', 'manager', 'reseller'])): ?>
            <th>Profit</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody id="recentOtpsBody">
          <tr>
            <td colspan="7" class="text-center text-muted py-4">
              <span class="spinner-border spinner-border-sm me-2"></span>Loading…
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Announcements (for resellers/sub-resellers) -->
<?php if (in_array($role, ['reseller', 'sub_reseller'])): ?>
<div class="card mt-3 chart-animate" style="--delay:.5s">
  <div class="card-header"><i class="ri-megaphone-line me-1 text-primary"></i>Announcements</div>
  <div class="card-body">
    <?php
    $pdo  = getDB();
    $news = $pdo->query(
        "SELECT n.*, u.username FROM news_master n
         JOIN users u ON n.created_by = u.id
         ORDER BY n.created_at DESC LIMIT 5"
    )->fetchAll();
    if (empty($news)): ?>
      <p class="text-muted mb-0">No announcements yet.</p>
    <?php else: foreach ($news as $item): ?>
      <div class="mb-3 pb-3 border-bottom">
        <h6 class="mb-1 fw-semibold"><?= h($item['title']) ?></h6>
        <p class="mb-1 text-muted" style="font-size:.875rem;"><?= nl2br(h($item['content'])) ?></p>
        <small class="text-muted">
          <i class="ri-user-line me-1"></i><?= h($item['username']) ?>
          &mdash; <?= h($item['created_at']) ?>
        </small>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>
<?php endif; ?>

<?php
$showProfit = in_array($role, ['admin', 'manager', 'reseller']) ? 'true' : 'false';
$appUrl     = APP_URL;
$csrfToken  = csrfToken();
?>
<script>
var APP_URL    = "<?= h($appUrl) ?>";
var CSRF_TOKEN = "<?= h($csrfToken) ?>";
var SHOW_PROFIT = <?= $showProfit ?>;

// ── Load stats ──────────────────────────────────────────────────────────────
$.getJSON(APP_URL + '/ajax/dashboard_stats.php', function(d) {
    if (d.status !== 'success') return;
    var s = d.data;
    animateCount('s-today-sms',     s.today_sms);
    animateCount('s-week-sms',      s.week_sms);
    animateCount('s-total-numbers', s.total_numbers);
    animateCount('s-total-users',   s.total_users);
    $('#s-today-profit').text('$' + parseFloat(s.today_profit).toFixed(4));
    $('#s-week-profit').text('$'  + parseFloat(s.week_profit).toFixed(4));
});

// ── SMS Area Chart ───────────────────────────────────────────────────────────
$.getJSON(APP_URL + '/ajax/dashboard_charts.php?type=sms', function(d) {
    if (!d.categories) return;
    new ApexCharts(document.querySelector('#chartSms'), {
        chart: {
            type: 'area', height: 260,
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 800 },
        },
        series: [{ name: 'SMS Received', data: d.data }],
        xaxis: { categories: d.categories, labels: { style: { fontSize: '11px', colors: '#6c757d' } } },
        yaxis: { labels: { style: { colors: '#6c757d' } } },
        colors: ['#4f46e5'],
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.02, stops: [0, 100] }
        },
        stroke: { curve: 'smooth', width: 2.5 },
        markers: { size: 5, colors: ['#4f46e5'], strokeColors: '#fff', strokeWidth: 2 },
        tooltip: { y: { formatter: function(v) { return v + ' SMS'; } } },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        dataLabels: { enabled: false },
    }).render();
});

// ── Services Donut Chart ─────────────────────────────────────────────────────
$.getJSON(APP_URL + '/ajax/dashboard_charts.php?type=services', function(d) {
    if (!d.labels || !d.labels.length) {
        document.querySelector('#chartServices').innerHTML =
            '<div class="text-center text-muted py-5"><i class="ri-pie-chart-line" style="font-size:2.5rem;opacity:.3;"></i><p class="mt-2">No data yet</p></div>';
        return;
    }
    new ApexCharts(document.querySelector('#chartServices'), {
        chart: {
            type: 'donut', height: 260,
            animations: { enabled: true, easing: 'easeinout', speed: 800 },
        },
        series: d.data,
        labels: d.labels,
        colors: ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444'],
        legend: { position: 'bottom', fontSize: '12px' },
        dataLabels: { enabled: true, formatter: function(v) { return v.toFixed(1) + '%'; } },
        tooltip: { y: { formatter: function(v) { return v + ' SMS'; } } },
        plotOptions: { pie: { donut: { size: '65%' } } },
    }).render();
});

// ── Recent OTPs ──────────────────────────────────────────────────────────────
$.getJSON(APP_URL + '/ajax/dashboard_stats.php?recent=1', function(d) {
    var tbody = $('#recentOtpsBody');
    if (!d.recent || d.recent.length === 0) {
        tbody.html('<tr><td colspan="7" class="text-center text-muted py-4"><i class="ri-inbox-line me-1"></i>No OTPs received yet.</td></tr>');
        return;
    }
    var html = '';
    d.recent.forEach(function(r) {
        html += '<tr>';
        html += '<td><small class="text-muted">' + (r.received_at || '') + '</small></td>';
        html += '<td><code class="text-primary">' + (r.number || '') + '</code></td>';
        html += '<td><span class="badge bg-info text-dark">' + (r.service || '–') + '</span></td>';
        html += '<td><span class="badge bg-secondary">' + (r.country || '–') + '</span></td>';
        html += '<td><strong class="text-dark">' + (r.otp || '') + '</strong></td>';
        html += '<td><small class="text-muted">' + ((r.message || '').substring(0, 60) + ((r.message || '').length > 60 ? '…' : '')) + '</small></td>';
        if (SHOW_PROFIT) {
            html += '<td>' + (r.profit ? '<span class="text-success fw-semibold">$' + parseFloat(r.profit).toFixed(6) + '</span>' : '<span class="text-muted">–</span>') + '</td>';
        }
        html += '</tr>';
    });
    tbody.html(html);
});

// ── Fetch OTPs trigger ───────────────────────────────────────────────────────
function triggerFetch() {
    var btn = document.getElementById('fetchBtn');
    if (!btn) return;
    btn.classList.add('loading');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Fetching…';

    $.getJSON(APP_URL + '/ajax/cron_fetch.php', function(d) {
        btn.classList.remove('loading');
        btn.disabled = false;
        btn.innerHTML = '<i class="ri-refresh-line me-1"></i>Fetch OTPs Now';
        if (d.status === 'success') {
            showToast('✅ Fetched! New SMS: ' + d.new_count, 'success');
            setTimeout(function() { location.reload(); }, 1500);
        } else {
            showToast(d.message || 'Fetch failed', 'warning');
        }
    }).fail(function() {
        btn.classList.remove('loading');
        btn.disabled = false;
        btn.innerHTML = '<i class="ri-refresh-line me-1"></i>Fetch OTPs Now';
        showToast('Fetch request failed', 'danger');
    });
}

// ── Animated counter ─────────────────────────────────────────────────────────
function animateCount(id, target) {
    var el = document.getElementById(id);
    if (!el) return;
    var start = 0, duration = 800, step = target / (duration / 16);
    var timer = setInterval(function() {
        start += step;
        if (start >= target) { start = target; clearInterval(timer); }
        el.textContent = Math.floor(start).toLocaleString();
    }, 16);
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
