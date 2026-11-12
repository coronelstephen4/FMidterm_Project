<div class="no-print flex flex-wrap items-center gap-2">
    <a href="/FMidterm_Project/reports/" class="rounded-md border border-slate-300 px-4 py-2 font-semibold hover:bg-white">All reports</a>
    <?php if (!empty($printAllUrl)): ?>
        <a href="<?= htmlspecialchars($printAllUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
    <?php else: ?>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
    <?php endif; ?>
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 8.25V3.75h10.5v4.5m-10.5 8.25h-1.5A2.25 2.25 0 0 1 3 14.25v-3.75a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 10.5v3.75a2.25 2.25 0 0 1-2.25 2.25h-1.5m-10.5-3h10.5v6.75H6.75V13.5Zm11.25-3.75h.008v.008H18v-.008Z" />
        </svg>
        Print report
    <?php if (!empty($printAllUrl)): ?></a><?php else: ?></button><?php endif; ?>
</div>
