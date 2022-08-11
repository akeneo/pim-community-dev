<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\SQL;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetTemplateSqlIntegration extends TestCase
{
    public function testGetTemplateById(): void
    {
        // TODO: update test creating category and template
        $template = $this->get(GetTemplate::class)->byUuid('bdhbvld');
        $this->assertNull($template);
    }

    public function testGetNoTemplateByWrongId(): void
    {
        // TODO: update test creating category and template
        $template = $this->get(GetTemplate::class)->byUuid('qvuioqiubvpfa');
        $this->assertNull($template);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
