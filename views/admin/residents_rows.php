<?php
declare(strict_types=1);

/**
 * Zeppelin Suites - Resident Table Row Partial
 * Rendered during both initial page loads and AJAX live filter requests.
 */
$baseUrl = $baseUrl ?? rtrim((string)env('APP_URL', '/Zeppelin-Suites'), '/');
$userId = (int)($resident['user_id'] ?? 0);
$fullName = htmlspecialchars((string)($resident['full_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars((string)($resident['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$contact = htmlspecialchars((string)(!empty($resident['contact']) ? $resident['contact'] : '—'), ENT_QUOTES, 'UTF-8');
$rawRole = (string)($resident['user_role'] ?? 'tenant');
$role = htmlspecialchars($rawRole === 'unit owner' ? 'Unit Owner' : ucfirst($rawRole), ENT_QUOTES, 'UTF-8');
$createdAt = !empty($resident['created_at']) ? date('M d, Y', strtotime((string)$resident['created_at'])) : '—';
$status = trim((string)($resident['resident_status'] ?? 'Active'));
$viewResidentUrl = $baseUrl . '/admin/residents/view?id=' . $userId;
?>
<tr class="emp-row cursor-pointer hover:bg-slate-50/80 transition-colors" data-status="<?= htmlspecialchars(strtolower($status), ENT_QUOTES, 'UTF-8') ?>" onclick="window.location.href='<?= htmlspecialchars($viewResidentUrl) ?>'">
    <td class="px-5 py-3.5 font-semibold emp-name text-slate-800 whitespace-nowrap align-middle"><?= $fullName ?></td>
    <td class="px-4 py-3.5 text-slate-500 text-xs whitespace-nowrap align-middle"><?= $email ?></td>
    <td class="px-4 py-3.5 text-center text-slate-600 text-xs whitespace-nowrap align-middle" style="font-family:'DM Mono',monospace"><?= $contact ?></td>
    <td class="px-4 py-3.5 text-center text-slate-600 text-xs font-medium whitespace-nowrap align-middle"><?= $role ?></td>
    <td class="px-4 py-3.5 text-center text-slate-500 text-xs whitespace-nowrap align-middle" style="font-family:'DM Mono',monospace"><?= $createdAt ?></td>
    <td class="px-4 py-3.5 text-center align-middle whitespace-nowrap">
        <?php if ($status === 'Active'): ?>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full inline-flex items-center bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
        <?php else: ?>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full inline-flex items-center bg-slate-100 text-slate-500 border border-slate-200">Inactive</span>
        <?php endif; ?>
    </td>
    <?php if (!empty($isSuperAdmin)): ?>
    <td class="px-4 py-3.5 text-center align-middle whitespace-nowrap" onclick="event.stopPropagation()">
        <form action="<?= htmlspecialchars($baseUrl) ?>/admin/residents/impersonate" method="POST" class="inline">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(class_exists('Csrf') ? Csrf::token() : '') ?>">
            <input type="hidden" name="user_id" value="<?= $userId ?>">
            <button type="submit" class="btn-press inline-flex items-center gap-1.5 px-3 py-1 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold rounded-full shadow-2xs transition-all active:scale-95" title="Visit Resident Portal">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Visit
            </button>
        </form>
    </td>
    <?php endif; ?>
</tr>
