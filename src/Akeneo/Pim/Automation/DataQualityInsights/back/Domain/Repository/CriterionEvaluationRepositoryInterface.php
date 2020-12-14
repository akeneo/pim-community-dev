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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

interface CriterionEvaluationRepositoryInterface
{
    public function create(Write\CriterionEvaluationCollection $criteriaEvaluations): void;

    public function update(Write\CriterionEvaluationCollection $criteriaEvaluations): void;
}
