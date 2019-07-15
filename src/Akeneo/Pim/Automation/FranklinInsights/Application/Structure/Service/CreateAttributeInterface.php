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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface CreateAttributeInterface
{
    public function create(
        AttributeCode $attributeCode,
        AttributeLabel $attributeLabel,
        AttributeType $attributeType
    ): void;

    public function bulkCreate(array $attributesToCreate): void;
}
