-- Professional navbar visibility control per top-level category.
-- nav_mode: auto = show only when the category (or its subtree) has active products
--           show = force visible in the navbar regardless of product count
--           hide = force hidden in the navbar (admin controlled)
ALTER TABLE categories
    ADD COLUMN nav_mode ENUM('auto','show','hide') NOT NULL DEFAULT 'auto' AFTER is_active,
    ADD COLUMN nav_priority INT NOT NULL DEFAULT 0 AFTER nav_mode;

-- Reset priority for sensible ordering; admins can reorder later.
UPDATE categories SET nav_priority = position WHERE nav_priority = 0;

-- Restore previously hidden groups to 'auto' so they can appear once products exist
-- or be forced on from the admin panel.
UPDATE categories
SET nav_mode = 'auto'
WHERE slug IN ('computers-laptops','cameras-drones','home-smart-devices','laptops','desktops','monitors');
