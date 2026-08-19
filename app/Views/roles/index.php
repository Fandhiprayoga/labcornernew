<div class="page__section">
  <div class="card">
    <div class="card__header">
      <span class="card__title">Daftar Role</span>
    </div>
    <div class="card__body p-0">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th style="width: 60px;">#</th>
              <th>Role</th>
              <th>Deskripsi</th>
              <th>Permissions</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; foreach ($groups as $key => $group): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td>
                <?php
                  $badgeClass = match($key) {
                    'superadmin' => 'danger',
                    'kepala_lab' => 'warning',
                    'laboran'    => 'info',
                    'asisten_lab' => 'success',
                    'user'       => 'primary',
                    default      => 'primary',
                  };
                ?>
                <span class="badge badge--soft badge--<?= $badgeClass ?>"><?= esc($group['title']) ?></span>
              </td>
              <td><?= esc($group['description']) ?></td>
              <td>
                <?php if (isset($matrix[$key])): ?>
                  <?php foreach ($matrix[$key] as $perm): ?>
                    <span class="badge badge--soft badge--secondary mr-1 mb-1"><?= esc($perm) ?></span>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="text-muted-foreground">Tidak ada permission</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
