UPGRADE FROM 0.1.5 to 0.1.6
===========================

Run the following SQL commands in your database :

    ALTER TABLE akeneo_batch_job_execution ADD pid INT DEFAULT NULL;

UPGRADE FROM 0.1.6+ to 0.2
==========================

Run the following SQL commands in your database :

    CREATE TABLE akeneo_batch_warning (id INT AUTO_INCREMENT NOT NULL, step_execution_id INT DEFAULT NULL, name VARCHAR(100) DEFAULT NULL, reason LONGTEXT DEFAULT NULL, reason_parameters LONGTEXT NOT NULL COMMENT '(DC2Type:array)', item LONGTEXT NOT NULL COMMENT '(DC2Type:array)', INDEX IDX_8EE0AE736C7DA296 (step_execution_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
    ALTER TABLE akeneo_batch_warning ADD CONSTRAINT FK_8EE0AE736C7DA296 FOREIGN KEY (step_execution_id) REFERENCES akeneo_batch_step_execution (id) ON DELETE CASCADE;
    ALTER TABLE akeneo_batch_step_execution DROP warnings;
