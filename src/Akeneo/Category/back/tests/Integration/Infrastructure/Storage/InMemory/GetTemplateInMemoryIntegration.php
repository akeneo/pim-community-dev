<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\InMemory;

use Akeneo\Category\Application\Query\CheckTemplate;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CheckTemplateSqlIntegration extends CategoryTestCase
{
    public function testTemplateCodeExists(): void
    {
        $templateCode = new TemplateCode('default_template');
        $this->assertFalse($this->get(CheckTemplate::class)->codeExists($templateCode));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
