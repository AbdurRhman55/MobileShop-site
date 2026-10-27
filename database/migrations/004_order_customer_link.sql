-- Optional customer association for order history; guest orders remain valid.
ALTER TABLE orders
    ADD COLUMN customer_id INT UNSIGNED NULL AFTER reference,
    ADD KEY idx_order_customer (customer_id),
    ADD CONSTRAINT fk_order_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL;
