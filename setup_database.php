<?php
/**
 * RMS Database Setup Script
 * Run once to create all tables and seed admin/teacher accounts.
 */

$host    = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'rms';

try {
    // Connect without selecting a database first
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $results = [];

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db_name`");
    $results[] = "✅ Database '$db_name' ready.";

    // 1. USER table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user` (
          `id`         INT(11)      NOT NULL AUTO_INCREMENT,
          `name`       VARCHAR(255) NOT NULL,
          `email`      VARCHAR(255) NOT NULL,
          `username`   VARCHAR(255) NOT NULL,
          `password`   VARCHAR(255) NOT NULL,
          `role`       ENUM('Teacher', 'Admin') NOT NULL DEFAULT 'Teacher',
          `phone`      VARCHAR(255) NULL,
          `department` VARCHAR(255) NULL,
          `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_by` INT(11)      NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_user_email`    (`email`),
          UNIQUE KEY `uq_user_username` (`username`),
          KEY `fk_user_updated_by` (`updated_by`),
          CONSTRAINT `fk_user_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `user`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $results[] = "✅ Table 'user' ready.";

    // 2. BATCH table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `batch` (
          `id`         INT(11)      NOT NULL AUTO_INCREMENT,
          `batch_year` INT(11)      NULL DEFAULT NULL,
          `batch_name` VARCHAR(255) NOT NULL,
          `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_by` INT(11)      NULL,
          PRIMARY KEY (`id`),
          KEY `fk_batch_updated_by` (`updated_by`),
          CONSTRAINT `fk_batch_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `user`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $results[] = "✅ Table 'batch' ready.";

    // 3. SUBJECT table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `subject` (
          `subject_code` VARCHAR(255) NOT NULL,
          `name`         VARCHAR(255) NOT NULL,
          `department`   VARCHAR(255) NOT NULL,
          `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_by`   INT(11)      NULL,
          PRIMARY KEY (`subject_code`),
          KEY `fk_subject_updated_by` (`updated_by`),
          CONSTRAINT `fk_subject_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `user`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $results[] = "✅ Table 'subject' ready.";

    // 4. ROOM table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `room` (
          `id`          INT(11)      NOT NULL AUTO_INCREMENT,
          `room_number` VARCHAR(255) NOT NULL,
          `capacity`    INT(11)      NULL,
          `projector`   TINYINT(1)   NULL DEFAULT 0,
          `smart_board` TINYINT(1)   NULL DEFAULT 0,
          `white_board` TINYINT(1)   NULL DEFAULT 0,
          `ac`          TINYINT(1)   NULL DEFAULT 0,
          `fan`         TINYINT(1)   NULL DEFAULT 0,
          `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_by`  INT(11)      NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_room_number` (`room_number`),
          KEY `fk_room_updated_by` (`updated_by`),
          CONSTRAINT `fk_room_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `user`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $results[] = "✅ Table 'room' ready.";

    // 5. TIME_SLOT table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `time_slot` (
          `id`          INT(11)      NOT NULL AUTO_INCREMENT,
          `start_time`  TIME         NOT NULL,
          `end_time`    TIME         NOT NULL,
          `day_of_week` VARCHAR(20)  NULL,
          `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_by`  INT(11)      NULL,
          PRIMARY KEY (`id`),
          KEY `fk_ts_updated_by` (`updated_by`),
          CONSTRAINT `fk_ts_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `user`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $results[] = "✅ Table 'time_slot' ready.";

    // 6. ROUTINE table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `routine` (
          `id`           INT(11)      NOT NULL AUTO_INCREMENT,
          `day_of_week`  VARCHAR(255) NULL,
          `subject_code` VARCHAR(255) NOT NULL,
          `teacher_id`   INT(11)      NOT NULL,
          `batch_id`     INT(11)      NOT NULL,
          `room_id`      INT(11)      NOT NULL,
          `time_slot_id` INT(11)      NOT NULL,
          `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_by`   INT(11)      NULL,
          PRIMARY KEY (`id`),
          KEY `fk_routine_subject`    (`subject_code`),
          KEY `fk_routine_teacher`    (`teacher_id`),
          KEY `fk_routine_batch`      (`batch_id`),
          KEY `fk_routine_room`       (`room_id`),
          KEY `fk_routine_timeslot`   (`time_slot_id`),
          KEY `fk_routine_updated_by` (`updated_by`),
          CONSTRAINT `fk_routine_subject`    FOREIGN KEY (`subject_code`) REFERENCES `subject`(`subject_code`) ON UPDATE CASCADE,
          CONSTRAINT `fk_routine_teacher`    FOREIGN KEY (`teacher_id`)   REFERENCES `user`(`id`)             ON DELETE RESTRICT,
          CONSTRAINT `fk_routine_batch`      FOREIGN KEY (`batch_id`)     REFERENCES `batch`(`id`)            ON DELETE RESTRICT,
          CONSTRAINT `fk_routine_room`       FOREIGN KEY (`room_id`)      REFERENCES `room`(`id`)             ON DELETE RESTRICT,
          CONSTRAINT `fk_routine_timeslot`   FOREIGN KEY (`time_slot_id`) REFERENCES `time_slot`(`id`)        ON DELETE RESTRICT,
          CONSTRAINT `fk_routine_updated_by` FOREIGN KEY (`updated_by`)   REFERENCES `user`(`id`)             ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $results[] = "✅ Table 'routine' ready.";

    // 7. TEACHER_ROOM_REQUEST table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `teacher_room_request` (
          `id`           INT(11)      NOT NULL AUTO_INCREMENT,
          `teacher_id`   INT(11)      NOT NULL,
          `batch_id`     INT(11)      NULL,
          `day_of_week`  VARCHAR(20)  NOT NULL,
          `subject_code` VARCHAR(255) NULL,
          `room_id`      INT(11)      NOT NULL,
          `time_slot_id` INT(11)      NOT NULL,
          `reason`       TEXT         NULL,
          `status`       VARCHAR(20)  NOT NULL DEFAULT 'Pending',
          `reject_reason` TEXT        NULL,
          `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `fk_trr_teacher`  (`teacher_id`),
          KEY `fk_trr_room`     (`room_id`),
          KEY `fk_trr_timeslot` (`time_slot_id`),
          KEY `fk_trr_batch`    (`batch_id`),
          CONSTRAINT `fk_trr_teacher`  FOREIGN KEY (`teacher_id`)   REFERENCES `user`(`id`)      ON DELETE CASCADE,
          CONSTRAINT `fk_trr_room`     FOREIGN KEY (`room_id`)      REFERENCES `room`(`id`)      ON DELETE CASCADE,
          CONSTRAINT `fk_trr_timeslot` FOREIGN KEY (`time_slot_id`) REFERENCES `time_slot`(`id`) ON DELETE CASCADE,
          CONSTRAINT `fk_trr_batch`    FOREIGN KEY (`batch_id`)     REFERENCES `batch`(`id`)     ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $results[] = "✅ Table 'teacher_room_request' ready.";

    // 8. LEAVE_REQUEST table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `leave_request` (
          `id`            INT(11)     NOT NULL AUTO_INCREMENT,
          `teacher_id`    INT(11)     NOT NULL,
          `routine_id`    INT(11)     NOT NULL,
          `day_of_week`   VARCHAR(20) NOT NULL,
          `reason`        TEXT        NOT NULL,
          `status`        VARCHAR(20) NOT NULL DEFAULT 'Pending',
          `admin_remarks` TEXT        NULL,
          `created_at`    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_by`    INT(11)     NULL,
          PRIMARY KEY (`id`),
          KEY `fk_lr_teacher` (`teacher_id`),
          KEY `fk_lr_routine` (`routine_id`),
          KEY `fk_lr_updated_by` (`updated_by`),
          CONSTRAINT `fk_lr_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `user`(`id`)    ON DELETE CASCADE,
          CONSTRAINT `fk_lr_routine` FOREIGN KEY (`routine_id`) REFERENCES `routine`(`id`) ON DELETE CASCADE,
          CONSTRAINT `fk_lr_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `user`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $results[] = "✅ Table 'leave_request' ready.";

    // 9. STUDENT table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `student` (
          `user_id`    INT(11)   NOT NULL,
          `semester`   INT(11)   NOT NULL,
          `batch_id`   INT(11)   NOT NULL,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_by` INT(11)   NULL,
          PRIMARY KEY (`user_id`),
          KEY `fk_student_batch`      (`batch_id`),
          KEY `fk_student_updated_by` (`updated_by`),
          CONSTRAINT `fk_student_user`       FOREIGN KEY (`user_id`)    REFERENCES `user`(`id`)  ON DELETE CASCADE,
          CONSTRAINT `fk_student_batch`      FOREIGN KEY (`batch_id`)   REFERENCES `batch`(`id`) ON DELETE CASCADE,
          CONSTRAINT `fk_student_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `user`(`id`)  ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $results[] = "✅ Table 'student' ready.";

    // 10. STUDENT_SUBJECT table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `student_subject` (
          `student_id`   INT(11)      NOT NULL,
          `subject_code` VARCHAR(255) NOT NULL,
          `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_by`   INT(11)      NULL,
          PRIMARY KEY (`student_id`, `subject_code`),
          KEY `fk_ss_subject`    (`subject_code`),
          KEY `fk_ss_updated_by` (`updated_by`),
          CONSTRAINT `fk_ss_student`    FOREIGN KEY (`student_id`)   REFERENCES `student`(`user_id`)     ON DELETE CASCADE,
          CONSTRAINT `fk_ss_subject`    FOREIGN KEY (`subject_code`) REFERENCES `subject`(`subject_code`) ON DELETE CASCADE,
          CONSTRAINT `fk_ss_updated_by` FOREIGN KEY (`updated_by`)   REFERENCES `user`(`id`)              ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $results[] = "✅ Table 'student_subject' ready.";

    // =========================================================
    // SEED: Admin (a@gmail.com) and Teacher (m@gmail.com)
    // Password for both: 1234  (bcrypt hashed)
    // =========================================================
    $password_hash = password_hash('1234', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO `user` (name, email, username, password, role)
        VALUES (:name, :email, :username, :password, :role)
        ON DUPLICATE KEY UPDATE
            name     = VALUES(name),
            password = VALUES(password),
            role     = VALUES(role)
    ");

    $stmt->execute([
        ':name'     => 'Admin',
        ':email'    => 'a@gmail.com',
        ':username' => 'a@gmail.com',
        ':password' => $password_hash,
        ':role'     => 'Admin',
    ]);
    $results[] = "✅ Admin user 'a@gmail.com' (password: 1234) inserted/updated.";

    $stmt->execute([
        ':name'     => 'Teacher',
        ':email'    => 'm@gmail.com',
        ':username' => 'm@gmail.com',
        ':password' => $password_hash,
        ':role'     => 'Teacher',
    ]);
    $results[] = "✅ Teacher user 'm@gmail.com' (password: 1234) inserted/updated.";

    // Show tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $users  = $pdo->query("SELECT id, name, email, role FROM user")->fetchAll();

    echo "<pre style='font-family:monospace; font-size:14px; background:#0f172a; color:#e2e8f0; padding:2rem; border-radius:8px;'>";
    echo "<strong style='color:#38bdf8; font-size:18px;'>🗄️ RMS Database Setup Results</strong>\n\n";
    foreach ($results as $r) {
        echo $r . "\n";
    }
    echo "\n<strong style='color:#a3e635;'>Tables in '$db_name':</strong>\n";
    foreach ($tables as $t) echo "  • $t\n";
    echo "\n<strong style='color:#a3e635;'>User accounts:</strong>\n";
    foreach ($users as $u) {
        echo "  • [ID:{$u['id']}] {$u['name']} &lt;{$u['email']}&gt; — Role: {$u['role']}\n";
    }
    echo "\n<strong style='color:#fbbf24;'>⚠️  Setup complete! Delete setup_database.php for security.</strong>";
    echo "</pre>";

} catch (PDOException $e) {
    echo "<pre style='color:red; padding:2rem;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>
