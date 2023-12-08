<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231207120609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE emails_list (id INT AUTO_INCREMENT NOT NULL, email_name VARCHAR(255) NOT NULL, email_id VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE global_settings (id INT AUTO_INCREMENT NOT NULL, damping DOUBLE PRECISION DEFAULT NULL, scrollimg DOUBLE PRECISION DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, pos INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE menu_link (id INT AUTO_INCREMENT NOT NULL, menu_id INT DEFAULT NULL, page_id INT DEFAULT NULL, post_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, menu_link_id INT DEFAULT NULL, order_link INT NOT NULL, cus_name JSON DEFAULT NULL, cus_link JSON DEFAULT NULL, blank TINYINT(1) DEFAULT NULL, INDEX IDX_FEE369BFCCD7E912 (menu_id), INDEX IDX_FEE369BFC4663E4 (page_id), INDEX IDX_FEE369BF4B89032C (post_id), INDEX IDX_FEE369BF727ACA70 (parent_id), INDEX IDX_FEE369BF257F1FCF (menu_link_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pages_list (id INT AUTO_INCREMENT NOT NULL, page_name JSON NOT NULL, page_url VARCHAR(255) NOT NULL, page_id VARCHAR(255) NOT NULL, blocked_page TINYINT(1) NOT NULL, status TINYINT(1) NOT NULL, page_content JSON DEFAULT NULL, page_meta_title JSON DEFAULT NULL, page_meta_desc JSON NOT NULL, main_page TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE posts_list (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, post_name JSON NOT NULL, post_content JSON NOT NULL, post_meta_title JSON NOT NULL, post_meta_desc JSON DEFAULT NULL, post_url VARCHAR(255) NOT NULL, post_thumb VARCHAR(255) DEFAULT NULL, online TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FE98C1A1F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE menu_link ADD CONSTRAINT FK_FEE369BFCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE menu_link ADD CONSTRAINT FK_FEE369BFC4663E4 FOREIGN KEY (page_id) REFERENCES pages_list (id)');
        $this->addSql('ALTER TABLE menu_link ADD CONSTRAINT FK_FEE369BF4B89032C FOREIGN KEY (post_id) REFERENCES posts_list (id)');
        $this->addSql('ALTER TABLE menu_link ADD CONSTRAINT FK_FEE369BF727ACA70 FOREIGN KEY (parent_id) REFERENCES menu_link (id)');
        $this->addSql('ALTER TABLE menu_link ADD CONSTRAINT FK_FEE369BF257F1FCF FOREIGN KEY (menu_link_id) REFERENCES menu_link (id)');
        $this->addSql('ALTER TABLE posts_list ADD CONSTRAINT FK_FE98C1A1F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_link DROP FOREIGN KEY FK_FEE369BFCCD7E912');
        $this->addSql('ALTER TABLE menu_link DROP FOREIGN KEY FK_FEE369BFC4663E4');
        $this->addSql('ALTER TABLE menu_link DROP FOREIGN KEY FK_FEE369BF4B89032C');
        $this->addSql('ALTER TABLE menu_link DROP FOREIGN KEY FK_FEE369BF727ACA70');
        $this->addSql('ALTER TABLE menu_link DROP FOREIGN KEY FK_FEE369BF257F1FCF');
        $this->addSql('ALTER TABLE posts_list DROP FOREIGN KEY FK_FE98C1A1F675F31B');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE emails_list');
        $this->addSql('DROP TABLE global_settings');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE menu_link');
        $this->addSql('DROP TABLE pages_list');
        $this->addSql('DROP TABLE posts_list');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
