<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;

interface GetAttributesCodesToEvaluateQueryInterface
{
    /** @return AttributeCode[] */
    public function execute(): iterable;

    /** @return AttributeCode[] */
    public function toReevaluate(): iterable;
}
