-- Migration: rename columns to add _jnsa suffix
-- Prefer running migrations/apply_rename_jnsa.php, which skips columns already renamed.
-- NOTE: Types below are inferred from the visible table data in the screenshots.

-- loan_member_jn table
ALTER TABLE loan_member_jn CHANGE member_id member_id_jnsa INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE loan_member_jn CHANGE member_name member_name_jnsa VARCHAR(255) NOT NULL;
ALTER TABLE loan_member_jn CHANGE contact_information contact_information_jnsa VARCHAR(50) NOT NULL;
ALTER TABLE loan_member_jn CHANGE address address_jnsa VARCHAR(255) NOT NULL;
ALTER TABLE loan_member_jn CHANGE member_img member_img_jnsa VARCHAR(255) NOT NULL;
ALTER TABLE loan_member_jn CHANGE username username_jnsa VARCHAR(255) NOT NULL;
ALTER TABLE loan_member_jn CHANGE password password_jnsa VARCHAR(255) NOT NULL;
ALTER TABLE loan_member_jn CHANGE user_type user_type_jnsa VARCHAR(20) NOT NULL;
ALTER TABLE loan_member_jn CHANGE otp_jn otp_jnsa VARCHAR(10) NULL;
ALTER TABLE loan_member_jn CHANGE user_status_jn user_status_jnsa VARCHAR(20) NOT NULL DEFAULT 'Pending';

-- loan_logs_jn table
ALTER TABLE loan_logs_jn CHANGE log_id log_id_jnsa INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE loan_logs_jn CHANGE member_id member_id_jnsa INT(11) NOT NULL;
ALTER TABLE loan_logs_jn CHANGE action_jn action_jnsa VARCHAR(50) NOT NULL;
ALTER TABLE loan_logs_jn CHANGE datetime_jn datetime_jnsa DATETIME NOT NULL;

-- loan_type_jn table
ALTER TABLE loan_type_jn CHANGE loan_type_id loan_type_id_jnsa INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE loan_type_jn CHANGE loan_type_name loan_type_name_jnsa VARCHAR(100) NOT NULL;
ALTER TABLE loan_type_jn CHANGE description description_jnsa VARCHAR(255) NOT NULL;

