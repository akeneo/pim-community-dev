<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\back\tests\Integration\CategoryTemplateTrait;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;

class SqlCategoryTemplateSaverIntegration extends CategoryTestCase
{
    use CategoryTemplateTrait;

    public function testInsertNewCategoryTemplateInDatabase(): void
    {
        $templateModel = $this->generateStaticCategoryTemplate();

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);

        $insertedTemplate = $this->get(GetTemplate::class)->byUuid((string) $templateModel->getUuid());

        $this->assertEquals($templateModel->getCode(),$insertedTemplate->getCode());
        $this->assertEquals($templateModel->getLabelCollection(),$insertedTemplate->getLabelCollection());
    }


}
