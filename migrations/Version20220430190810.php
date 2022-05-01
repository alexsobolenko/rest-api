<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220430190810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tables for ToDo App: [todo__tasks], [todo__points]';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE `todo__tasks` (
            `id` VARCHAR(255) NOT NULL, 
            `title` VARCHAR(255) NOT NULL, 
            `priority` INT NOT NULL, 
            `created_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
            `updated_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
            PRIMARY KEY(`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE `todo__points` (
            `id` VARCHAR(255) NOT NULL, 
            `task_id` VARCHAR(255) DEFAULT NULL, 
            `title` VARCHAR(255) NOT NULL, 
            `completed` TINYINT(1) NOT NULL, 
            `created_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
            `updated_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
            INDEX IDX_B80A3DB8DB60186 (`task_id`), PRIMARY KEY(`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("ALTER TABLE `todo__points` ADD CONSTRAINT FK_B80A3DB8DB60186 FOREIGN KEY (`task_id`) 
            REFERENCES `todo__tasks` (`id`) ON DELETE CASCADE");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `todo__points` DROP FOREIGN KEY FK_B80A3DB8DB60186");
        $this->addSql("DROP TABLE `todo__tasks`");
        $this->addSql("DROP TABLE `todo__points`");
    }
}
