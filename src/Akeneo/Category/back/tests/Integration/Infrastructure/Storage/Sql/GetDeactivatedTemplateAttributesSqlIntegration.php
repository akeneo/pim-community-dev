<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Query\GetDeactivatedTemplateAttributes;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetDeactivatedTemplateAttributesSqlIntegration extends CategoryTestCase
{
    public const TEMPLATE_UUID = '8a2ee006-0459-42e3-a8fc-07d32b547b95';

    public function testGetAllDeactivatedTemplateAttributes(): void
    {
        $this->useTemplateFunctionalCatalog(self::TEMPLATE_UUID, 'test');

        $getDeactivatedTemplateAttributes = $this->get(GetDeactivatedTemplateAttributes::class);
        $deactivatedAttributes = $getDeactivatedTemplateAttributes->execute();
        $this->assertEmpty($deactivatedAttributes);

        $getAttributes = $this->get(GetAttribute::class);
        $attributes = $getAttributes->byTemplateUuid(TemplateUuid::fromString(self::TEMPLATE_UUID))->getAttributes();
        $attributeToBeDeactivated = $attributes[0]->normalize();
        $this->deactivateAttribute($attributeToBeDeactivated['uuid']);

        $deactivatedAttributes = $getDeactivatedTemplateAttributes->execute();
        $this->assertEquals($attributeToBeDeactivated['uuid'], $deactivatedAttributes[0]['attribute_uuid']);
        $this->assertEquals($attributeToBeDeactivated['code'], $deactivatedAttributes[0]['code']);
    }
}
