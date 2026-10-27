-- Add payment_screenshot to orders table
ALTER TABLE orders
    ADD COLUMN payment_screenshot VARCHAR(255) NULL AFTER payment_reference;

-- Update settings for Soneri Bank and WhatsApp
UPDATE settings SET setting_value = 'Soneri Bank' WHERE setting_key = 'payments_bank_name';
UPDATE settings SET setting_value = 'Lala Mobiles' WHERE setting_key = 'payments_account_title';
UPDATE settings SET setting_value = 'PK62SONE0000320013557234' WHERE setting_key = 'payments_account_number';
UPDATE settings SET setting_value = '+92 300 5970504' WHERE setting_key = 'organization_phone';
UPDATE settings SET setting_value = 'Soneri Bank (Bank Transfer)' WHERE setting_key = 'payment_methods';
UPDATE settings SET setting_value = 'Free nationwide shipping on orders over Rs 5,000|/page/delivery-information
Soneri Bank online transfer available
Warranty & 7-day returns on every device|/page/warranty-return-policy' WHERE setting_key = 'announcement_items';
UPDATE settings SET setting_value = 'Buy new, used and refurbished mobile phones in Pakistan at best prices. Samsung, iPhone, Xiaomi & more with warranty and nationwide shipping via Soneri Bank transfer.' WHERE setting_key = 'default_seo_description';
