<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240404173717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Auditor and Job tables';
    }

    public function up(Schema $schema): void
    {
        $this->createAuditorTable($schema);
        $this->createJobTable($schema);
    }

    private function createAuditorTable(Schema $schema): void
    {
        $table = $schema->createTable('auditor');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('location', 'string', ['length' => 255]);
        $table->addColumn('timezone', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
    }

    private function createJobTable(Schema $schema): void
    {
        $table = $schema->createTable('job');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('auditor_id', 'integer');
        $table->addColumn('title', 'string', ['length' => 255]);
        $table->addColumn('description', 'text');
        $table->addColumn('date', 'date');
        $table->addColumn('completed', 'boolean');
        $table->addColumn('assessment', 'text', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('auditor', ['auditor_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('job');
        $schema->dropTable('auditor');
    }
}
