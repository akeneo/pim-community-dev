<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Query;

use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface GetAttribute
{
    public function byTemplateUuid(TemplateUuid $uuid): AttributeCollection;

    /**
     * @param AttributeUuid[] $attributeUuids
     */
    public function byUuids(array $attributeUuids): AttributeCollection;

    public function byUuid(AttributeUuid $attributeUuid): ?Attribute;

    public function byCode(AttributeCode $attributeCode): Attribute;
}
