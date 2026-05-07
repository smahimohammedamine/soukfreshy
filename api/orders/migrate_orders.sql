-- Run this once on an existing soukfreshy database to add checkout delivery fields.
ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS consumer_phone   VARCHAR(20) NULL AFTER consumer_name,
  ADD COLUMN IF NOT EXISTS delivery_address TEXT        NULL AFTER commune,
  MODIFY COLUMN commune VARCHAR(100) NOT NULL DEFAULT '';