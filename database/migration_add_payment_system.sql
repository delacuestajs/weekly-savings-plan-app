-- Add payment_system to users (1=week number, 2=fixed)
ALTER TABLE users ADD COLUMN payment_system TINYINT NOT NULL DEFAULT 1 AFTER multiplier;

-- Add fixed_amount to bags (default fixed monthly payment)
ALTER TABLE bags ADD COLUMN fixed_amount DECIMAL(12,2) NOT NULL DEFAULT 50000.00 AFTER status;
