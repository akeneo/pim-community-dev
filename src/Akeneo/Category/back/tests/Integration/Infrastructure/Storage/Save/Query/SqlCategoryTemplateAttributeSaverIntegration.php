<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;

class SqlCategoryTemplateAttributeSaverIntegration extends CategoryTestCase
{
    public function testInsertNewCategoryAttributeInDatabase(): void
    {
        $templateModel = $this->generateMockedCategoryTemplateModel();

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);

        $this->get(CategoryTemplateAttributeSaver::class)->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection()
        );

        $insertedTemplate = $this->get(GetTemplate::class)->byUuid((string) $templateModel->getUuid());

        $this->assertEqualsCanonicalizing(
            array_keys($templateModel->getAttributeCollection()->getAttributes()),
            array_keys($insertedTemplate->getAttributeCollection()->getAttributes())
        );
    }


}
