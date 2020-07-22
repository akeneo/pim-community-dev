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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Entity;

/**
 * For the sole purpose of retrieving attribute quality evaluation from a Doctrine ORM query builder (for the attributes grid).
 */
class AttributeQuality
{
    public $attributeCode;
    public $quality;
}
