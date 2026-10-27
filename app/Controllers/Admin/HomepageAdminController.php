<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\HttpRedirect;

/**
 * Homepage section manager.
 *
 * The storefront already honours homepage_sections (position + is_active +
 * optional title/subtitle override) via Homepage::activeSectionOrder(); this
 * controller is the UI that writes those columns — reorder, enable/disable and
 * retitle the thirteen A–M sections without touching a template.
 *
 * Everything posts to a single endpoint. Two submit-button families share the
 * form: `_move` (id:up / id:down) reorders by swapping positions with the
 * neighbour; otherwise the visible field values are persisted.
 */
final class HomepageAdminController extends AdminBaseController
{
    public function index(): void
    {
        $rows = Database::fetchAll(
            'SELECT section_key, title, subtitle, position, is_active
             FROM homepage_sections ORDER BY position, id'
        );

        $this->renderAdmin('admin/homepage/index', [
            'rows'  => $rows,
            'flash' => $this->takeFlash(),
        ]);
    }

    public function update(): void
    {
        $move = trim((string) $this->request->input('_move', ''));
        if ($move !== '') {
            $parts = explode(':', $move, 2);
            $id    = (int) ($parts[0] ?? 0);
            $dir   = (string) ($parts[1] ?? '');

            if ($id > 0 && in_array($dir, ['up', 'down'], true) && $this->swap($id, $dir)) {
                $this->flash('Section order updated.');
            } else {
                $this->flash('That section is already at the top or bottom.', 'warning');
            }
            HttpRedirect::to('/admin/homepage', 303);
            return;
        }

        // Fields are keyed by section id (not positional), so a row that omits
        // a visible input can never shift values onto the wrong section.
        $ids       = (array) $this->request->input('sec_id', []);
        $titles    = (array) $this->request->input('sec_title', []);
        $subs      = (array) $this->request->input('sec_subtitle', []);
        $positions = (array) $this->request->input('sec_position', []);
        $active    = (array) $this->request->input('sec_active', []);

        $saved = 0;
        foreach ($ids as $rawId) {
            $id = (int) $rawId;
            if ($id <= 0) {
                continue;
            }
            $title    = mb_substr(trim((string) ($titles[$id] ?? '')), 0, 160);
            $subtitle = mb_substr(trim((string) ($subs[$id] ?? '')), 0, 320);
            $pos      = (int) ($positions[$id] ?? 0);
            // Hidden "0" + checkbox "1" share a name, so the key always exists.
            // !empty() is required — the literal string "0" is truthy via isset().
            $isActive = !empty($active[$id]) ? 1 : 0;

            Database::query(
                'UPDATE homepage_sections
                    SET title = ?, subtitle = ?, position = ?, is_active = ?
                  WHERE id = ?',
                [$title !== '' ? $title : null, $subtitle !== '' ? $subtitle : null, $pos, $isActive, $id]
            );
            $saved++;
        }

        $this->flash("Homepage layout saved — {$saved} section" . ($saved === 1 ? '' : 's') . " updated.");
        HttpRedirect::to('/admin/homepage', 303);
    }

    /**
     * Swap a section with its neighbour in the current display order.
     *
     * Positions are exchanged rather than recomputed, so existing gaps in the
     * numbering are preserved and no other row is touched.
     */
    private function swap(int $id, string $dir): bool
    {
        $rows = Database::fetchAll(
            'SELECT id FROM homepage_sections ORDER BY position, id'
        );

        $index = -1;
        foreach ($rows as $i => $r) {
            if ((int) $r['id'] === $id) {
                $index = $i;
                break;
            }
        }
        if ($index < 0) {
            return false;
        }

        $target = $dir === 'up' ? $index - 1 : $index + 1;
        if ($target < 0 || $target >= count($rows)) {
            return false; // already first / last
        }

        $aId = (int) $rows[$index]['id'];
        $bId = (int) $rows[$target]['id'];

        $aPos = (int) Database::fetchColumn('SELECT position FROM homepage_sections WHERE id = ?', [$aId]);
        $bPos = (int) Database::fetchColumn('SELECT position FROM homepage_sections WHERE id = ?', [$bId]);

        if ($aPos === $bPos) {
            // Tied positions: fall back to an explicit order by nudging one.
            $bPos = $aPos + ($dir === 'up' ? -1 : 1);
        }

        Database::query('UPDATE homepage_sections SET position = ? WHERE id = ?', [$bPos, $aId]);
        Database::query('UPDATE homepage_sections SET position = ? WHERE id = ?', [$aPos, $bId]);

        return true;
    }
}
