<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Query;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeLabel;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
interface SelectExactMatchAttributeCodeQueryInterface
{
    public function execute(FamilyCode $familyCode, array $franklinAttributeLabel): array;
}
