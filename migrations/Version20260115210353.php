<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260115210353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE articles (
        id VARCHAR(255) NOT NULL, 
        title VARCHAR(500) NOT NULL, 
        description LONGTEXT NOT NULL, 
        content LONGTEXT NOT NULL, 
        url VARCHAR(500) NOT NULL, 
        image_url VARCHAR(500) DEFAULT NULL, 
        published_at DATETIME NOT NULL, 
        source_name VARCHAR(255) NOT NULL, 
        created_at DATETIME NOT NULL, 
        UNIQUE INDEX UNIQ_BFDD3168F47645AE (url), 
        INDEX idx_articles_url (url), 
        INDEX idx_articles_published_at (published_at), 
        PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE articles');
    }
}
