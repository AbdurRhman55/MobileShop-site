<?php /** @var array $rows */ ?>
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-[12px] font-extrabold uppercase tracking-[0.18em] text-electric-600">Search</p>
        <h2 class="mt-1 text-[24px] font-extrabold tracking-tight text-ink">Redirect manager</h2>
    </div>
    <span class="badge badge-soft px-3 py-1.5"><?= count($rows) ?> active</span>
</div>

<div class="mt-5 grid gap-5 lg:grid-cols-3">
    <!-- ============================ Add form ============================ -->
    <aside class="lg:col-span-1">
        <div class="card p-5">
            <h3 class="text-[15px] font-extrabold text-ink">Add redirect</h3>

            <form method="post" action="/admin/seo/redirects" class="mt-4 space-y-4">
                <?= \App\Core\Csrf::field() ?>

                <div>
                    <label for="from_path" class="mb-1.5 block text-[13px] font-bold text-ink">Source path <span class="text-flame-500">*</span></label>
                    <input type="text" id="from_path" name="from_path" required maxlength="512"
                           placeholder="/old-path" class="input font-mono !text-[13px]">
                </div>

                <div>
                    <label for="to_path" class="mb-1.5 block text-[13px] font-bold text-ink">Destination path <span class="text-flame-500">*</span></label>
                    <input type="text" id="to_path" name="to_path" required maxlength="512"
                           placeholder="/new-path" class="input font-mono !text-[13px]">
                    <p class="field-hint">Same-site paths only — external URLs are rejected (open-redirect protection).</p>
                </div>

                <div>
                    <label for="status_code" class="mb-1.5 block text-[13px] font-bold text-ink">Status</label>
                    <select id="status_code" name="status_code" class="input">
                        <option value="301">301 — Permanent (use for moved pages)</option>
                        <option value="302">302 — Temporary</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-md btn-block">Create redirect</button>
            </form>

            <div class="mt-5 space-y-2 border-t border-line pt-4 text-[12.5px] leading-relaxed text-ink-muted">
                <p class="text-[11.5px] font-bold uppercase tracking-wide text-ink">Automatic redirects</p>
                <p>Changing a product, category, brand, blog or page slug in its edit form automatically creates a 301 from the old URL.</p>
                <p>Deleted products 301 to their replacement product when set, otherwise return a genuine 410 Gone.</p>
            </div>
        </div>
    </aside>

    <!-- ============================= List ============================= -->
    <section class="card overflow-hidden lg:col-span-2" aria-labelledby="redirects-heading">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
            <h3 id="redirects-heading" class="text-[15px] font-extrabold text-ink">Active redirects</h3>
            <span class="text-[13px] text-ink-muted"><?= count($rows) ?> total</span>
        </div>

        <?php if ($rows === []): ?>
        <div class="p-12 text-center">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-surface-muted text-ink-muted">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-7 w-7"><path d="M9 14 4 9l5-5M4 9h11a5 5 0 0 1 0 10h-4"/></svg>
            </span>
            <p class="mt-4 text-[15px] font-bold text-ink">No redirects yet</p>
            <p class="mx-auto mt-1.5 max-w-sm text-[13.5px] text-ink-muted">Slug changes and manual entries appear here.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="data-table">
                <caption class="sr-only">All URL redirects</caption>
                <thead>
                    <tr>
                        <th scope="col">From</th>
                        <th scope="col">To</th>
                        <th scope="col">Code</th>
                        <th scope="col">Reason</th>
                        <th scope="col"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td data-label="From" class="break-all font-mono text-[12.5px] text-ink"><?= e($r['from_path']) ?></td>
                        <td data-label="To" class="break-all font-mono text-[12.5px]">
                            <a href="<?= e($r['to_path']) ?>" class="font-semibold text-electric-600 hover:text-electric-700"><?= e($r['to_path']) ?></a>
                        </td>
                        <td data-label="Code">
                            <span class="badge <?= (int) $r['status_code'] === 301 ? 'bg-jade-50 text-jade-700' : 'bg-gold-100 text-navy-900' ?> px-2.5 py-1 !text-[11.5px]">
                                <?= (int) $r['status_code'] ?>
                            </span>
                        </td>
                        <td data-label="Reason" class="text-ink-muted"><?= e($r['reason'] ?? '') ?></td>
                        <td data-label="" class="text-right">
                            <form method="post" action="/admin/seo/redirects/<?= (int) $r['id'] ?>/delete"
                                  onsubmit="return confirm('Delete this redirect?');">
                                <?= \App\Core\Csrf::field() ?>
                                <button type="submit" class="btn btn-ghost btn-sm text-flame-600 hover:bg-flame-50">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
</div>
