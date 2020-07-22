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

/**
 * Note that the table `pimee_dqi_attribute_spellcheck` is created by Doctrine. See: AttributeSpellcheck.orm.yml
 * Because we need to declare a Doctrine ORM entity to alter the query builder of the attribute-grid to fill the column "Quality"
 */
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

CREATE TABLE pimee_dqi_attribute_spellcheck (
    attribute_code VARCHAR(100) NOT NULL PRIMARY KEY,
    evaluated_at DATETIME NOT NULL,
    to_improve TINYINT(1) DEFAULT NULL,
    result JSON NOT NULL,
    INDEX evaluated_at_index (evaluated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pimee_dqi_attribute_option_spellcheck (
    attribute_code VARCHAR(100) NOT NULL,
    attribute_option_code VARCHAR(100) NOT NULL,
    evaluated_at DATETIME NOT NULL,
    to_improve TINYINT NULL,
    result JSON NOT NULL,
    PRIMARY KEY attribute_option_key (attribute_code, attribute_option_code),
    INDEX evaluated_at_index (evaluated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
}
