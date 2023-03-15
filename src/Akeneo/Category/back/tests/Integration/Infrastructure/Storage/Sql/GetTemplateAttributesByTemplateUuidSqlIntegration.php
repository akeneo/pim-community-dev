<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetTemplateAttributesByTemplateUuid;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetTemplateAttributesByTemplateUuidSqlIntegration extends CategoryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->useTemplateFunctionalCatalog('6344aa2a-2be9-4093-b644-259ca7aee50c', 'socks');

        $attributesUuids = $this->generateRandomUuidList(13);
        $this->useTemplateFunctionalCatalog('1294e06d-48a4-4055-abda-986d92bef8a2', 'pants', $attributesUuids);
    }

    public function testItRetrievesOnlyAttributesRelatedToTemplate(): void
    {
        $getTemplateAttributesByTemplateUuid = $this->get(GetTemplateAttributesByTemplateUuid::class);
        $attributeCodes = $getTemplateAttributesByTemplateUuid->execute('6344aa2a-2be9-4093-b644-259ca7aee50c');
        Assert::assertCount(13, $attributeCodes);
        Assert::assertArrayHasKey('840fcd1a-f66b-4f0c-9bbd-596629732950', $attributeCodes);
        Assert::assertEquals('long_description', $attributeCodes['840fcd1a-f66b-4f0c-9bbd-596629732950']);
    }
}
