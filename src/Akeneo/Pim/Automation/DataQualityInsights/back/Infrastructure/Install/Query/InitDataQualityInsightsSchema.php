<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\Query;

final class InitDataQualityInsightsSchema
{
    const QUERY = <<<'SQL'
CREATE TABLE pimee_data_quality_insights_product_criteria_evaluation (
  product_id int NOT NULL,
  criterion_code varchar(40) NOT NULL,
  evaluated_at datetime NULL,
  status varchar(15) NOT NULL,
  result json DEFAULT NULL,
  PRIMARY KEY (product_id, criterion_code),
  INDEX status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pimee_data_quality_insights_product_model_criteria_evaluation (
  product_id int NOT NULL,
  criterion_code varchar(40) NOT NULL,
  evaluated_at datetime NULL,
  status varchar(15) NOT NULL,
  result json DEFAULT NULL,
  PRIMARY KEY (product_id, criterion_code),
  INDEX status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pimee_data_quality_insights_product_axis_rates (
    product_id INT NOT NULL,
    axis_code VARCHAR(40) NOT NULL,
    evaluated_at DATE NOT NULL,
    rates JSON NOT NULL,
    PRIMARY KEY (product_id, axis_code, evaluated_at),
    INDEX evaluated_at_index (evaluated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pimee_data_quality_insights_product_model_axis_rates (
    product_id INT NOT NULL,
    axis_code VARCHAR(40) NOT NULL,
    evaluated_at DATE NOT NULL,
    rates JSON NOT NULL,
    PRIMARY KEY (product_id, axis_code, evaluated_at),
    INDEX evaluated_at_index (evaluated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pimee_data_quality_insights_dashboard_rates_projection (
    type VARCHAR(15) NOT NULL,
    code VARCHAR(100) NOT NULL,
    rates JSON NOT NULL,
    PRIMARY KEY (type, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pimee_data_quality_insights_text_checker_dictionary (
    locale_code VARCHAR(20) NOT NULL,
    word VARCHAR(250) NOT NULL,
    INDEX word_index (word)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
}
