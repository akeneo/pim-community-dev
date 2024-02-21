<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CriterionEvaluationRepositoryInterface
{
    public function create(Write\CriterionEvaluationCollection $criteriaEvaluations): void;

    public function update(Write\CriterionEvaluationCollection $criteriaEvaluations): void;
}
