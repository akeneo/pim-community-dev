<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\InMemory;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Storage\InMemory\GetTemplateInMemory;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetTemplateInMemoryIntegration extends CategoryTestCase
{
    public function testGetTemplateInMemoryById(): void
    {
        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');

        $expectedTemplate = new Template(
            uuid: $templateUuid,
            code: new TemplateCode('default_template'),
            labelCollection: LabelCollection::fromArray(['en_US' => 'Default template']),
            categoryTreeId: new CategoryId(1),
            attributeCollection: null
        );
        $template = $this->get(GetTemplateInMemory::class)->byUuid($templateUuid);
        $this->assertEquals($expectedTemplate, $template);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
