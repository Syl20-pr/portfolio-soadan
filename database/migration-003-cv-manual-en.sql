-- Additively stores manual English names for skills so CV publication works without an API key.
ALTER TABLE `skills`
    ADD COLUMN `name_en` VARCHAR(120) NOT NULL DEFAULT '' AFTER `name`;
