<?php
$currentUser = auth()->user();
$groups = $currentUser->getGroups();
$groupLabel = activeGroupTitle();
$calendarMonthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
$requestedMonth = (int) service('request')->getGet('month');
$requestedYear = (int) service('request')->getGet('year');
$calendarToday = new DateTimeImmutable('first day of this month');
$calendarMonth = $requestedMonth >= 1 && $requestedMonth <= 12 ? $requestedMonth : (int) $calendarToday->format('n');
$calendarYear = $requestedYear >= 2000 && $requestedYear <= 2100 ? $requestedYear : (int) $calendarToday->format('Y');
$calendarDate = new DateTimeImmutable(sprintf('%04d-%02d-01', $calendarYear, $calendarMonth));
$calendarMonth = (int) $calendarDate->format('n');
$calendarYear = (int) $calendarDate->format('Y');
$calendarFirstWeekday = (int) $calendarDate->format('N');
$calendarDaysInMonth = (int) $calendarDate->format('t');
$calendarPrevious = $calendarDate->modify('-1 month');
$calendarNext = $calendarDate->modify('+1 month');
$calendarUrl = static fn (DateTimeImmutable $date): string => base_url('dashboard') . '?month=' . $date->format('n') . '&year=' . $date->format('Y');
$calendarStatusLabels = ['approved' => 'Disetujui', 'completed' => 'Selesai'];
$calendarFormatDate = static fn (string $value): string => date('d M Y H:i', strtotime($value));
$calendarEventsByDay = [];
$calendarEvents = [];
foreach ($loanEvents ?? [] as $loanEvent) {
  $eventStart = new DateTimeImmutable($loanEvent['event_start']);
  $eventEnd = new DateTimeImmutable($loanEvent['event_end']);
  $monthStart = $calendarDate;
  $monthEnd = $calendarDate->modify('last day of this month')->setTime(23, 59, 59);
  if ($eventEnd < $monthStart || $eventStart > $monthEnd) {
    continue;
  }
  $calendarEvents[] = $loanEvent;
  $eventDay = $eventStart < $monthStart ? $monthStart : $eventStart;
  while ($eventDay <= $eventEnd && $eventDay <= $monthEnd) {
    $calendarEventsByDay[(int) $eventDay->format('j')][] = $loanEvent;
    $eventDay = $eventDay->modify('+1 day');
  }
}
?>

<div class="page__section">
  <div class="card dashboard-calendar">
    <div class="card__header">
      <div>
        <span class="card__title">Kalender Peminjaman Laboratorium</span>
        <div class="text-xs text-muted-foreground">Jadwal yang sudah disetujui dan selesai</div>
      </div>
      <div class="card__action flex items-center gap-2">
        <span class="badge badge--soft badge--success">Disetujui</span>
        <span class="badge badge--soft badge--primary">Selesai</span>
      </div>
    </div>
    <div class="card__body">
      <style>
        .dashboard-calendar__grid { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); border-top:1px solid var(--color-border); border-left:1px solid var(--color-border); }
        .dashboard-calendar__weekday { padding:.5rem; background:var(--color-muted); color:var(--color-muted-foreground); font-size:.7rem; font-weight:600; text-align:center; border-right:1px solid var(--color-border); border-bottom:1px solid var(--color-border); }
        .dashboard-calendar__day { min-height:7rem; padding:.5rem; border-right:1px solid var(--color-border); border-bottom:1px solid var(--color-border); }
        .dashboard-calendar__day--empty { background:var(--color-muted); opacity:.45; }
        .dashboard-calendar__number { display:block; margin-bottom:.35rem; font-size:.8rem; font-weight:600; }
        .dashboard-calendar__event { display:block; width:100%; margin-top:.25rem; padding:.25rem .35rem; overflow:hidden; color:inherit; font:inherit; font-size:.7rem; line-height:1.25; text-align:left; text-decoration:none; white-space:nowrap; text-overflow:ellipsis; border:0; border-left:3px solid var(--color-success); cursor:pointer; background:color-mix(in srgb, var(--color-success) 12%, transparent); }
        .dashboard-calendar__event[data-status="completed"] { border-left-color:var(--color-primary); background:color-mix(in srgb, var(--color-primary) 12%, transparent); }
        .dashboard-calendar__toolbar { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:1rem; }
        .dashboard-calendar__filters { display:flex; align-items:center; gap:.5rem; }
        .dashboard-calendar__form { flex:1 1 auto; min-width:0; }
        .dashboard-calendar__form .select { min-width:0; flex:1 1 auto; }
        .dashboard-calendar__form .input { flex:0 0 6rem; min-height:2.25rem; }
        .dashboard-calendar__form .button { flex:0 0 auto; white-space:nowrap; }
        @media (max-width: 40rem) { .dashboard-calendar__day { min-height:5rem; padding:.3rem; } .dashboard-calendar__event { font-size:.62rem; } .dashboard-calendar__weekday { padding:.35rem .1rem; } .dashboard-calendar__toolbar { align-items:stretch; flex-direction:column; } .dashboard-calendar__toolbar > .dashboard-calendar__filters { justify-content:space-between; } .dashboard-calendar__form { width:100%; } }
      </style>
      <div class="dashboard-calendar__toolbar">
        <div class="dashboard-calendar__filters">
          <a class="button button--outline button--neutral button--icon-only button--sm" href="<?= esc($calendarUrl($calendarPrevious), 'attr') ?>" title="Bulan sebelumnya" aria-label="Bulan sebelumnya">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m15 6-6 6 6 6" /></svg>
          </a>
          <form method="get" action="<?= base_url('dashboard') ?>" class="dashboard-calendar__filters dashboard-calendar__form">
            <label class="sr-only" for="calendar-month">Bulan</label>
            <select class="select" id="calendar-month" name="month" aria-label="Pilih bulan">
              <?php foreach ($calendarMonthNames as $monthNumber => $monthName): ?><option value="<?= $monthNumber ?>" <?= $calendarMonth === $monthNumber ? 'selected' : '' ?>><?= esc($monthName) ?></option><?php endforeach; ?>
            </select>
            <label class="sr-only" for="calendar-year">Tahun</label>
            <input class="input" id="calendar-year" name="year" type="number" min="2000" max="2100" value="<?= $calendarYear ?>" aria-label="Pilih tahun">
            <button class="button button--primary button--sm" type="submit">Tampilkan</button>
          </form>
          <a class="button button--outline button--neutral button--icon-only button--sm" href="<?= esc($calendarUrl($calendarNext), 'attr') ?>" title="Bulan berikutnya" aria-label="Bulan berikutnya">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 6 6 6-6 6" /></svg>
          </a>
        </div>
        <span class="text-xs text-muted-foreground"><?= count($calendarEvents) ?> kegiatan</span>
      </div>
      <div class="dashboard-calendar__grid" aria-label="Kalender <?= esc($calendarMonthNames[$calendarMonth] . ' ' . $calendarYear) ?>">
        <?php foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday): ?><div class="dashboard-calendar__weekday"><?= $weekday ?></div><?php endforeach; ?>
        <?php for ($emptyDay = 1; $emptyDay < $calendarFirstWeekday; $emptyDay++): ?><div class="dashboard-calendar__day dashboard-calendar__day--empty" aria-hidden="true"></div><?php endfor; ?>
        <?php for ($day = 1; $day <= $calendarDaysInMonth; $day++): ?>
          <div class="dashboard-calendar__day">
            <span class="dashboard-calendar__number"><?= $day ?></span>
            <?php foreach ($calendarEventsByDay[$day] ?? [] as $loanEvent): ?>
              <button type="button" class="dashboard-calendar__event" data-status="<?= esc($loanEvent['status']) ?>" data-event-name="<?= esc($loanEvent['event_name'], 'attr') ?>" data-event-applicant="<?= esc($loanEvent['full_name'], 'attr') ?>" data-event-start="<?= esc($calendarFormatDate($loanEvent['event_start']), 'attr') ?>" data-event-end="<?= esc($calendarFormatDate($loanEvent['event_end']), 'attr') ?>" data-event-status="<?= esc($calendarStatusLabels[$loanEvent['status']] ?? ucfirst($loanEvent['status']), 'attr') ?>" data-event-detail-url="<?= esc(base_url('peminjaman/lab-loans/detail/' . $loanEvent['uuid']), 'attr') ?>" title="Lihat detail <?= esc($loanEvent['event_name'], 'attr') ?>">
                <?= esc($loanEvent['event_name']) ?>
              </button>
            <?php endforeach; ?>
          </div>
        <?php endfor; ?>
      </div>
      <?php if (empty($calendarEvents)): ?><p class="text-sm text-muted-foreground text-center" style="margin:1rem 0 0;">Belum ada jadwal peminjaman yang disetujui atau selesai pada bulan ini.</p><?php endif; ?>
    </div>
  </div>
</div>

<div class="dialog dialog--sm" id="dashboardLoanDetail" data-stisla-dialog data-state="closed" role="dialog" aria-modal="true" aria-labelledby="dashboardLoanDetailTitle" aria-hidden="true" tabindex="-1">
  <div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
  <div class="dialog__panel">
    <div class="dialog__content">
      <button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M6 6l12 12M18 6 6 18" /></svg>
      </button>
      <div class="dialog__header">
        <div>
          <h3 class="dialog__title" id="dashboardLoanDetailTitle" data-calendar-detail="event-name">Detail Peminjaman</h3>
          <div class="text-xs text-muted-foreground">Detail jadwal peminjaman laboratorium</div>
        </div>
        <span class="badge badge--soft badge--success" data-calendar-detail="status">-</span>
      </div>
      <div class="dialog__body">
        <div class="grid grid-cols-1 gap-4">
          <div><div class="text-xs text-muted-foreground">Pemohon</div><strong data-calendar-detail="applicant">-</strong></div>
          <div><div class="text-xs text-muted-foreground">Waktu Mulai</div><strong data-calendar-detail="start">-</strong></div>
          <div><div class="text-xs text-muted-foreground">Waktu Selesai</div><strong data-calendar-detail="end">-</strong></div>
        </div>
      </div>
      <div class="dialog__footer">
        <button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
  (() => {
    const dialog = document.getElementById('dashboardLoanDetail');
    if (!dialog) return;

    document.querySelectorAll('.dashboard-calendar__event').forEach((eventButton) => {
      eventButton.addEventListener('click', () => {
        dialog.querySelector('[data-calendar-detail="event-name"]').textContent = eventButton.dataset.eventName || '-';
        dialog.querySelector('[data-calendar-detail="applicant"]').textContent = eventButton.dataset.eventApplicant || '-';
        dialog.querySelector('[data-calendar-detail="start"]').textContent = eventButton.dataset.eventStart || '-';
        dialog.querySelector('[data-calendar-detail="end"]').textContent = eventButton.dataset.eventEnd || '-';
        const statusBadge = dialog.querySelector('[data-calendar-detail="status"]');
        statusBadge.textContent = eventButton.dataset.eventStatus || '-';
        statusBadge.classList.toggle('badge--primary', eventButton.dataset.status === 'completed');
        statusBadge.classList.toggle('badge--success', eventButton.dataset.status !== 'completed');
        dialog.dataset.state = 'open';
        dialog.setAttribute('aria-hidden', 'false');
      });
    });

    dialog.addEventListener('click', (event) => {
      if (!event.target.closest('[data-stisla-dialog-dismiss]')) return;
      dialog.dataset.state = 'closed';
      dialog.setAttribute('aria-hidden', 'true');
    });
  })();
</script>

<div class="page__section">
  <div class="mb-4">
    <h2 class="text-xl font-semibold">Selamat Datang, <?= esc($currentUser->username) ?>!</h2>
    <p class="text-muted-foreground mt-1">
      Anda login sebagai <span class="font-medium"><?= $groupLabel ?></span>.
      <?php if (count($groups) > 1): ?>
        <small class="text-muted-foreground">(Memiliki <?= count($groups) ?> role. Gunakan switcher di navbar untuk beralih.)</small>
      <?php endif; ?>
    </p>
  </div>
</div>

<?php if (activeGroupCan('admin.access')): ?>
<div class="page__section">
  <div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
      <div class="card card--stat">
        <div class="card__body">
          <div class="flex justify-between items-center">
            <span class="icon-box icon-box--primary icon-box--lg">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                  <circle cx="12" cy="6" r="4" />
                  <path d="M20 17.5c0 2.485 0 4.5-8 4.5s-8-2.015-8-4.5S7.582 13 12 13s8 2.015 8 4.5Z" />
                </g>
              </svg>
            </span>
          </div>
          <div class="stat mt-3">
            <div class="stat__value"><?php $userModel = new \CodeIgniter\Shield\Models\UserModel(); echo $userModel->countAllResults(); ?></div>
            <div class="stat__label text-eyebrow">Total Users</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
      <div class="card card--stat">
        <div class="card__body">
          <div class="flex justify-between items-center">
            <span class="icon-box icon-box--danger icon-box--lg">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M2 16c0-2.828 0-4.243.879-5.121C3.757 10 5.172 10 8 10h8c2.828 0 4.243 0 5.121.879C22 11.757 22 13.172 22 16s0 4.243-.879 5.121C20.243 22 18.828 22 16 22H8c-2.828 0-4.243 0-5.121-.879C2 20.243 2 18.828 2 16" opacity=".5" />
                <path fill="currentColor" d="M12 18a2 2 0 1 0 0-4a2 2 0 0 0 0 4M6.75 8a5.25 5.25 0 0 1 10.5 0v2.004c.567.005 1.064.018 1.5.05V8a6.75 6.75 0 0 0-13.5 0v2.055a24 24 0 0 1 1.5-.051z" />
              </svg>
            </span>
          </div>
          <div class="stat mt-3">
            <div class="stat__value"><?= count(config('AuthGroups')->groups) ?></div>
            <div class="stat__label text-eyebrow">Total Roles</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
      <div class="card card--stat">
        <div class="card__body">
          <div class="flex justify-between items-center">
            <span class="icon-box icon-box--warning icon-box--lg">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M14.279 2.152C13.909 2 13.439 2 12.5 2s-1.408 0-1.779.152a2 2 0 0 0-1.09 1.083c-.094.223-.13.484-.145.863" opacity=".5" />
                <path fill="currentColor" d="M15.523 12c0 1.657-1.354 3-3.023 3s-3.023-1.343-3.023-3S10.83 9 12.5 9s3.023 1.343 3.023 3" />
              </svg>
            </span>
          </div>
          <div class="stat mt-3">
            <div class="stat__value"><?= count(config('AuthGroups')->permissions) ?></div>
            <div class="stat__label text-eyebrow">Total Permissions</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
      <div class="card card--stat">
        <div class="card__body">
          <div class="flex justify-between items-center">
            <span class="icon-box icon-box--success icon-box--lg">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m5 13l4 4L19 7" />
              </svg>
            </span>
          </div>
          <div class="stat mt-3">
            <div class="stat__value">Active</div>
            <div class="stat__label text-eyebrow">Status Sistem</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="page__section">
  <div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 lg:col-span-6">
      <div class="card">
        <div class="card__header">
          <span class="card__title">Informasi Akun</span>
        </div>
        <div class="card__body">
          <table class="table">
            <tbody>
              <tr>
                <th class="text-muted-foreground font-medium" style="width: 140px;">Username</th>
                <td><?= esc($currentUser->username) ?></td>
              </tr>
              <tr>
                <th class="text-muted-foreground font-medium">Email</th>
                <td><?= esc($currentUser->email) ?></td>
              </tr>
              <tr>
                <th class="text-muted-foreground font-medium">Role</th>
                <td>
                  <?php foreach ($groups as $grp): ?>
                    <span class="badge badge--soft badge--primary mr-1"><?= esc(ucfirst($grp)) ?></span>
                  <?php endforeach; ?>
                </td>
              </tr>
              <tr>
                <th class="text-muted-foreground font-medium">Program Studi</th>
                <td>
                  <?php if ($currentStudyProgram): ?>
                    <?= esc($currentStudyProgram['name']) ?>
                  <?php else: ?>
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="badge badge--soft badge--warning">Belum diisi</span>
                      <a href="<?= site_url('profile') ?>" class="button button--primary button--icon-only button--sm" title="Isi Program Studi" aria-label="Isi Program Studi">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                          <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.5 4.5a2.121 2.121 0 0 1 3 3L7 21l-4 1l1-4Z" />
                        </svg>
                      </a>
                    </div>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <th class="text-muted-foreground font-medium">Nomor Telepon</th>
                <td>
                  <?php if (! empty($currentUser->phone)): ?>
                    <?= esc($currentUser->phone) ?>
                  <?php else: ?>
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="badge badge--soft badge--warning">Belum diisi</span>
                      <a href="<?= site_url('profile') ?>" class="button button--primary button--icon-only button--sm" title="Isi Nomor Telepon" aria-label="Isi Nomor Telepon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                          <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.5 4.5a2.121 2.121 0 0 1 3 3L7 21l-4 1l1-4Z" />
                        </svg>
                      </a>
                    </div>
                  <?php endif; ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
