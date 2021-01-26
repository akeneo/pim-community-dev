<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\EventSubscriber;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitDataQualityInsightsDbSchemaSubscriber implements EventSubscriberInterface
{
    private Connection $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'initDbSchema'
        ];
    }

    public function initDbSchema(InstallerEvent $event): void
    {
        $query = <<<'SQL'
CREATE TABLE pim_data_quality_insights_product_criteria_evaluation (
  product_id int NOT NULL,
  criterion_code varchar(40) NOT NULL,
  evaluated_at datetime NULL,
  status varchar(15) NOT NULL,
  result json DEFAULT NULL,
  PRIMARY KEY (product_id, criterion_code),
  INDEX status_index (status),
  CONSTRAINT FK_dqi_product_criteria_evaluation FOREIGN KEY (product_id) REFERENCES pim_catalog_product (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pim_data_quality_insights_product_model_criteria_evaluation (
  product_id int NOT NULL,
  criterion_code varchar(40) NOT NULL,
  evaluated_at datetime NULL,
  status varchar(15) NOT NULL,
  result json DEFAULT NULL,
  PRIMARY KEY (product_id, criterion_code),
  INDEX status_index (status),
  CONSTRAINT FK_dqi_product_model_criteria_evaluation FOREIGN KEY (product_id) REFERENCES pim_catalog_product_model (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pim_data_quality_insights_product_score (
    product_id INT NOT NULL,
    evaluated_at DATE NOT NULL,
    scores JSON NOT NULL,
    PRIMARY KEY (product_id, evaluated_at),
    INDEX evaluated_at_index (evaluated_at),
    CONSTRAINT FK_dqi_product_score FOREIGN KEY (product_id) REFERENCES pim_catalog_product (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pim_data_quality_insights_attribute_group_activation (
    attribute_group_code VARCHAR(100) NOT NULL PRIMARY KEY,
    activated TINYINT NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pim_data_quality_insights_dashboard_scores_projection (
    type VARCHAR(15) NOT NULL,
    code VARCHAR(100) NOT NULL,
    scores JSON NOT NULL,
    PRIMARY KEY (type, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $this->dbalConnection->executeQuery($query);
    }
}
