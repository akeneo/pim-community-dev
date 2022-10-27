<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\InMemory;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetTemplateInMemoryIntegration extends CategoryTestCase
{
    public function testGetTemplateById(): void
    {
        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $expectedTemplate = $this->generateMockedCategoryTemplateModel(
            templateUuid: $templateUuid
        );
        $template = $this->get(GetTemplate::class)->byUuid($templateUuid);
        $this->assertEquals($expectedTemplate, $template);
    }

    public function testTemplateExists(): void
    {
        $templateCode = new TemplateCode('default_template');
        $this->assertFalse($this->get(GetTemplate::class)->isAlreadyExistingTemplateCode($templateCode));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
