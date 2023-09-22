<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230903161600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE board (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, board_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_58562B4712469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, category_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, topic_id INT NOT NULL, content VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', image_file_name VARCHAR(255) DEFAULT NULL, INDEX IDX_B6BD307FA76ED395 (user_id), INDEX IDX_B6BD307F1F55203D (topic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, role_name VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE roles_categories (roles_id INT NOT NULL, categories_id INT NOT NULL, INDEX IDX_76D478E038C751C4 (roles_id), INDEX IDX_76D478E0A21214B7 (categories_id), PRIMARY KEY(roles_id, categories_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE topic (id INT AUTO_INCREMENT NOT NULL, board_id INT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, number_view INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', image_file_name VARCHAR(255) DEFAULT NULL, INDEX IDX_9D40DE1BE7EC5785 (board_id), INDEX IDX_9D40DE1BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, role_id INT NOT NULL, pseudo VARCHAR(180) NOT NULL, firstname VARCHAR(50) DEFAULT NULL, lastname VARCHAR(50) DEFAULT NULL, email VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_update DATE DEFAULT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D64986CC499D (pseudo), INDEX IDX_8D93D649D60322AC (role_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE board ADD CONSTRAINT FK_58562B4712469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id)');
        $this->addSql('ALTER TABLE roles_categories ADD CONSTRAINT FK_76D478E038C751C4 FOREIGN KEY (roles_id) REFERENCES roles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE roles_categories ADD CONSTRAINT FK_76D478E0A21214B7 FOREIGN KEY (categories_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1BE7EC5785 FOREIGN KEY (board_id) REFERENCES board (id)');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D60322AC FOREIGN KEY (role_id) REFERENCES roles (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board DROP FOREIGN KEY FK_58562B4712469DE2');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA76ED395');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F1F55203D');
        $this->addSql('ALTER TABLE roles_categories DROP FOREIGN KEY FK_76D478E038C751C4');
        $this->addSql('ALTER TABLE roles_categories DROP FOREIGN KEY FK_76D478E0A21214B7');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1BE7EC5785');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1BA76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D60322AC');
        $this->addSql('DROP TABLE board');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE roles_categories');
        $this->addSql('DROP TABLE topic');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
