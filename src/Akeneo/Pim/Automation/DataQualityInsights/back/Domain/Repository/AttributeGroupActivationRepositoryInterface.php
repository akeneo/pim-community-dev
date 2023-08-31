<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeGroupActivationRepositoryInterface
{
    public function save(AttributeGroupActivation $attributeGroupActivation): void;

    public function remove(AttributeGroupCode $attributeGroupCode): void;

    public function getForAttributeGroupCode(AttributeGroupCode $attributeGroupCode): ?AttributeGroupActivation;
}
