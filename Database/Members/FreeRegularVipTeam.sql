-- Drop the old FreeLevelMembers table if it exists
DROP TABLE IF EXISTS FreeLevelMembers;

-- Create FreeLevelMembers table with basic user info
CREATE TABLE FreeLevelMembers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tfUN VARCHAR(255) NOT NULL UNIQUE,   -- Username as the primary key for relation
    tfFN VARCHAR(255) NOT NULL,          -- First name
    tfLN VARCHAR(255) NOT NULL,          -- Last name
    tfNN VARCHAR(255),                   -- Nickname (Optional)
    tfGEN ENUM('Male', 'Female', 'Transgender', 'Transexual', 'Transvestite') NOT NULL,
    tfBirth DATE NOT NULL,               -- Birthday
    tfEM VARCHAR(255) NOT NULL,          -- Email
    tfPSW VARCHAR(255) NOT NULL,         -- Password
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create RegularMembers table 
CREATE TABLE RegularMembers (
    tfUN VARCHAR(100) PRIMARY KEY,
    ChineseZodiacSign VARCHAR(100),
    WesternZodiacSign VARCHAR(100),
    SpiritAnimal VARCHAR(100),
    CelticTreeZodiacSign VARCHAR(100),
    NativeAmericanZodiacSign VARCHAR(100),
    VedicAstrologySign VARCHAR(100),
    GuardianAngel VARCHAR(100),
    ChineseElement VARCHAR(100),
    EyeColorMeaning VARCHAR(100),
    GreekMythologyArchetype VARCHAR(100),
    NorseMythologyPatronDeity VARCHAR(100),
    EgyptianZodiacSign VARCHAR(100),
    MayanZodiacSign VARCHAR(100),
    FOREIGN KEY (tfUN) REFERENCES FreeLevelMembers(tfUN)
);
-- Create VIPMembers table for VIP users
CREATE TABLE VIPMembers (
    tfUN VARCHAR(100) PRIMARY KEY,  -- Foreign Key from FreeLevelMembers
    LoveLanguage VARCHAR(100),      -- Optional
    Birthstone VARCHAR(100),        -- Optional
    BirthFlower VARCHAR(100),       -- Optional
    BloodType VARCHAR(100),         -- Optional
    AttachmentStyle VARCHAR(100),   -- Optional
    CharismaType VARCHAR(100),      -- Optional
    FOREIGN KEY (tfUN) REFERENCES FreeLevelMembers(tfUN)
);

-- Create TeamMembers table for Team users
CREATE TABLE TeamMembers (
    tfUN VARCHAR(100) PRIMARY KEY,  -- Foreign Key from FreeLevelMembers
    BusinessPersonality VARCHAR(100),    -- Optional
    DISC VARCHAR(100),                  -- Optional
    SocionicsType VARCHAR(100),          -- Optional
    LearningStyle VARCHAR(100),          -- Optional
    FinancialPersonalityType VARCHAR(100),    -- Optional
    PrimaryMotivationStyle VARCHAR(100),    -- Optional
    CreativeStyle VARCHAR(100),        -- Optional
    ConflictManagementStyle VARCHAR(100),    -- Optional
    TeamRolePreference VARCHAR(100),     -- Optional
    FOREIGN KEY (tfUN) REFERENCES FreeLevelMembers(tfUN)
);

-- Create users table with extended payment and membership details
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    member_level ENUM('free', 'regular', 'vip', 'team') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',  -- Track payment status
    recurring_payment BOOLEAN DEFAULT FALSE,
    recurring_payment_type ENUM('monthly', 'yearly'),
    payment_amount DECIMAL(10, 2),
    first_payment_date DATE,
    last_payment_date DATE,  -- Track the most recent payment
    next_payment_date DATE,
    first_missed_payment DATE,
    second_missed_payment DATE,
    membership_status ENUM('active', 'canceled', 'revoked', 'suspended', 'terminated', 'failed'),
    membership_canceled_date DATE,
    membership_revoked_date DATE,
    membership_suspended_date DATE,
    membership_terminated_date DATE,
    membership_failed_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_payment_made BOOLEAN DEFAULT FALSE  -- Track if the last payment has been made
);

-- Stored Procedure to update next payment date and membership status
DELIMITER //

CREATE PROCEDURE update_membership_status()
BEGIN
    UPDATE users
    SET 
        payment_amount = CASE 
            WHEN member_level = 'free' THEN 0.00
            WHEN member_level = 'regular' THEN 30.00
            WHEN member_level = 'vip' AND recurring_payment_type = 'monthly' THEN 3.00
            WHEN member_level = 'vip' AND recurring_payment_type = 'yearly' THEN 30.00
            WHEN member_level = 'team' AND recurring_payment_type = 'monthly' THEN 10.00
            WHEN member_level = 'team' AND recurring_payment_type = 'yearly' THEN 100.00
            ELSE payment_amount
        END,
        next_payment_date = CASE 
            WHEN recurring_payment_type = 'monthly' THEN DATE_ADD(last_payment_date, INTERVAL 1 MONTH)
            WHEN recurring_payment_type = 'yearly' THEN DATE_ADD(last_payment_date, INTERVAL 1 YEAR)
            ELSE next_payment_date
        END,
        membership_status = CASE 
            WHEN payment_status = 'failed' THEN 'failed'
            ELSE membership_status
        END,
        membership_failed_date = CASE 
            WHEN payment_status = 'failed' THEN CURDATE()
            ELSE membership_failed_date
        END,
        last_payment_made = CASE 
            WHEN payment_status = 'completed' THEN TRUE
            ELSE FALSE
        END
    WHERE next_payment_date IS NULL OR next_payment_date < CURDATE();
END //

DELIMITER ;

-- Trigger to call the stored procedure after updating user payment info
DELIMITER //

CREATE TRIGGER after_user_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.last_payment_date IS NOT NULL THEN
        CALL update_membership_status();
    END IF;
END //

DELIMITER ;
