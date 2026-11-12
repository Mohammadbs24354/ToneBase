<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261112000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the initial ToneBase instrument catalog and audio metadata schema.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE instrument_families (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(120) NOT NULL, description TEXT DEFAULT NULL, category VARCHAR(30) NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_FAMILY_SLUG ON instrument_families (slug)");
        $this->addSql("CREATE TABLE instruments (id SERIAL NOT NULL, family_id INT NOT NULL, name VARCHAR(120) NOT NULL, slug VARCHAR(140) NOT NULL, description TEXT DEFAULT NULL, sound_type VARCHAR(40) NOT NULL, default_tuning VARCHAR(80) DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_INSTRUMENT_SLUG ON instruments (slug)");
        $this->addSql("CREATE INDEX IDX_INSTRUMENT_FAMILY ON instruments (family_id)");
        $this->addSql("CREATE TABLE sound_libraries (id SERIAL NOT NULL, name VARCHAR(140) NOT NULL, slug VARCHAR(160) NOT NULL, description TEXT DEFAULT NULL, status VARCHAR(30) NOT NULL, visibility VARCHAR(30) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_LIBRARY_SLUG ON sound_libraries (slug)");
        $this->addSql("CREATE TABLE presets (id SERIAL NOT NULL, instrument_id INT NOT NULL, library_id INT NOT NULL, name VARCHAR(140) NOT NULL, description TEXT DEFAULT NULL, settings JSON NOT NULL, status VARCHAR(30) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_PRESET_INSTRUMENT ON presets (instrument_id)");
        $this->addSql("CREATE INDEX IDX_PRESET_LIBRARY ON presets (library_id)");
        $this->addSql("CREATE TABLE audio_licenses (id SERIAL NOT NULL, name VARCHAR(120) NOT NULL, identifier VARCHAR(100) DEFAULT NULL, allows_redistribution BOOLEAN NOT NULL, attribution_required BOOLEAN NOT NULL, terms_url VARCHAR(500) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE TABLE audio_sources (id SERIAL NOT NULL, name VARCHAR(140) NOT NULL, source_url VARCHAR(500) DEFAULT NULL, attribution_text TEXT DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE TABLE audio_samples (id SERIAL NOT NULL, instrument_id INT NOT NULL, library_id INT NOT NULL, preset_id INT DEFAULT NULL, license_id INT NOT NULL, source_id INT NOT NULL, name VARCHAR(180) NOT NULL, storage_key VARCHAR(500) NOT NULL, format VARCHAR(12) NOT NULL, file_size_bytes BIGINT NOT NULL, checksum_sha256 VARCHAR(64) NOT NULL, midi_note INT DEFAULT NULL, octave INT DEFAULT NULL, velocity_min INT DEFAULT NULL, velocity_max INT DEFAULT NULL, root_note INT DEFAULT NULL, sample_rate INT DEFAULT NULL, duration_ms INT DEFAULT NULL, loop_start INT DEFAULT NULL, loop_end INT DEFAULT NULL, articulation VARCHAR(80) DEFAULT NULL, status VARCHAR(30) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_AUDIO_STORAGE_KEY ON audio_samples (storage_key)");
        $this->addSql("CREATE INDEX IDX_AUDIO_STATUS ON audio_samples (status)");
        $this->addSql("CREATE INDEX IDX_AUDIO_INSTRUMENT ON audio_samples (instrument_id)");
        $this->addSql("CREATE INDEX IDX_AUDIO_LIBRARY ON audio_samples (library_id)");
        $this->addSql("INSERT INTO instrument_families (id, name, slug, description, category, is_active, created_at) VALUES
            (1, 'Eastern Strings', 'eastern-strings', 'Traditional plucked and bowed string instruments from the Middle East.', 'eastern', TRUE, CURRENT_TIMESTAMP),
            (2, 'Eastern Winds', 'eastern-winds', 'Traditional Middle Eastern wind instruments.', 'eastern', TRUE, CURRENT_TIMESTAMP),
            (3, 'Western Strings', 'western-strings', 'Western acoustic string instruments.', 'western', TRUE, CURRENT_TIMESTAMP),
            (4, 'Western Winds', 'western-winds', 'Western wind and brass instruments.', 'western', TRUE, CURRENT_TIMESTAMP),
            (5, 'Keyboards', 'keyboards', 'Acoustic and electronic keyboard instruments.', 'keyboard', TRUE, CURRENT_TIMESTAMP),
            (6, 'Percussion', 'percussion', 'Hand percussion and drum instruments.', 'percussion', TRUE, CURRENT_TIMESTAMP),
            (7, 'Electronic Instruments', 'electronic-instruments', 'Electric and synthesized instruments.', 'electronic', TRUE, CURRENT_TIMESTAMP)");
        $this->addSql("INSERT INTO instruments (id, family_id, name, slug, description, sound_type, default_tuning, is_active, created_at) VALUES
            (1, 1, 'Oud', 'oud', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (2, 1, 'Qanun', 'qanun', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (3, 1, 'Buzuq', 'buzuq', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (4, 1, 'Arabic Violin', 'arabic-violin', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (5, 1, 'Rababa', 'rababa', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (6, 2, 'Ney', 'ney', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (7, 3, 'Violin', 'violin', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (8, 3, 'Cello', 'cello', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (9, 3, 'Classical Guitar', 'classical-guitar', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (10, 4, 'Flute', 'flute', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (11, 4, 'Saxophone', 'saxophone', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (12, 4, 'Trumpet', 'trumpet', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (13, 5, 'Piano', 'piano', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (14, 5, 'Electric Piano', 'electric-piano', NULL, 'electronic', NULL, TRUE, CURRENT_TIMESTAMP),
            (15, 5, 'Organ', 'organ', NULL, 'electronic', NULL, TRUE, CURRENT_TIMESTAMP),
            (16, 6, 'Daf', 'daf', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (17, 6, 'Riq', 'riq', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (18, 6, 'Tabla', 'tabla', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (19, 6, 'Drum Kit', 'drum-kit', NULL, 'acoustic', NULL, TRUE, CURRENT_TIMESTAMP),
            (20, 7, 'Electric Guitar', 'electric-guitar', NULL, 'electronic', NULL, TRUE, CURRENT_TIMESTAMP),
            (21, 7, 'Electric Bass', 'electric-bass', NULL, 'electronic', NULL, TRUE, CURRENT_TIMESTAMP),
            (22, 7, 'Synthesizer', 'synthesizer', NULL, 'electronic', NULL, TRUE, CURRENT_TIMESTAMP)");
        $this->addSql("ALTER SEQUENCE instrument_families_id_seq RESTART WITH 8");
        $this->addSql("ALTER SEQUENCE instruments_id_seq RESTART WITH 23");
        $this->addSql("ALTER TABLE instruments ADD CONSTRAINT FK_INSTRUMENT_FAMILY FOREIGN KEY (family_id) REFERENCES instrument_families (id) ON DELETE RESTRICT");
        $this->addSql("ALTER TABLE presets ADD CONSTRAINT FK_PRESET_INSTRUMENT FOREIGN KEY (instrument_id) REFERENCES instruments (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE presets ADD CONSTRAINT FK_PRESET_LIBRARY FOREIGN KEY (library_id) REFERENCES sound_libraries (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE audio_samples ADD CONSTRAINT FK_AUDIO_INSTRUMENT FOREIGN KEY (instrument_id) REFERENCES instruments (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE audio_samples ADD CONSTRAINT FK_AUDIO_LIBRARY FOREIGN KEY (library_id) REFERENCES sound_libraries (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE audio_samples ADD CONSTRAINT FK_AUDIO_PRESET FOREIGN KEY (preset_id) REFERENCES presets (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE audio_samples ADD CONSTRAINT FK_AUDIO_LICENSE FOREIGN KEY (license_id) REFERENCES audio_licenses (id) ON DELETE RESTRICT");
        $this->addSql("ALTER TABLE audio_samples ADD CONSTRAINT FK_AUDIO_SOURCE FOREIGN KEY (source_id) REFERENCES audio_sources (id) ON DELETE RESTRICT");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE audio_samples');
        $this->addSql('DROP TABLE presets');
        $this->addSql('DROP TABLE instruments');
        $this->addSql('DROP TABLE sound_libraries');
        $this->addSql('DROP TABLE audio_sources');
        $this->addSql('DROP TABLE audio_licenses');
        $this->addSql('DROP TABLE instrument_families');
    }
}
