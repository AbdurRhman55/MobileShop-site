-- Migration: 20261021_initial_performance_indexes
-- Additional indexes supporting sitemap generation, audit reports and
-- faceted-filter queries on top of the baseline schema.

ALTER TABLE products ADD KEY idx_product_slug_status (slug, status);
ALTER TABLE products ADD KEY idx_product_price (price);
ALTER TABLE blog_posts ADD KEY idx_blog_updated (updated_at);
ALTER TABLE pages ADD KEY idx_page_type_status (page_type, status);
ALTER TABLE seo_meta ADD KEY idx_seo_updated (updated_at);
