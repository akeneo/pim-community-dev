<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230811151128_add_check_json_schema_to_workflow_and_task_translation extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add check on the json format for translation field in akeneo_workflow and akeneo_workflow_task tables';
    }

    public function up(Schema $schema): void
    {
        $sqlCheckTranlationFormatWorkflow = <<<SQL
        ALTER TABLE akeneo_workflow
        ADD CONSTRAINT CHK_workflow_translation_json CHECK (
            JSON_SCHEMA_VALID(
                '{
                    "type": "object",
                    "patternProperties": {
                        "^[a-z]{2}(_[A-Z]{2})$": {
                            "type": "object",
                            "required": true,
                            "properties": {
                                "label": {
                                    "type": "string",
                                    "id": "label",
                                    "required": true
                                }
                            }
                        }
                    }
                }', translation
            )
        );
        SQL;

        $this->addSql($sqlCheckTranlationFormatWorkflow);

        $sqlCheckTranlationFormatTask = <<<SQL
        ALTER TABLE akeneo_workflow_task
        ADD CONSTRAINT CHK_workflow_task_translation_json CHECK (
            JSON_SCHEMA_VALID(
                '{
                    "type": "object",
                    "patternProperties": {
                        "^[a-z]{2}(_[A-Z]{2})$": {
                            "type": "object",
                            "required": true,
                            "properties": {
                                "label": {
                                    "type": "string",
                                    "id": "label",
                                    "required": true
                                },
                                "description": {
                                    "type": "string",
                                    "id": "description",
                                    "required": true
                                }
                            }
                        }
                    }
                }', translation
            )
        );
        SQL;

        $this->addSql($sqlCheckTranlationFormatTask);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
