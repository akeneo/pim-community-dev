<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;

class SqlCategoryTemplateAttributeSaverIntegration extends CategoryTestCase
{
    public function testInsertNewCategoryAttributeInDatabase(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $templateModel = $this->givenTemplateWithAttributes($templateUuid, $category->getId());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTemplateAttributeSaver::class)->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection()
        );

        /** @var Template $insertedTemplate */
        $insertedTemplate = $this->get(GetTemplate::class)->byUuid($templateModel->getUuid());

        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateModel->getUuid());
        $insertedTemplate->setAttributeCollection($insertedAttributes);

        $this->assertEqualsCanonicalizing(
            array_keys($templateModel->getAttributeCollection()->getAttributes()),
            array_keys($insertedTemplate->getAttributeCollection()->getAttributes())
        );
    }

    public function testItDoesNotInsertNewCategoryAttributeOnDeactivatedTemplate(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $templateModel = $this->givenTemplateWithAttributes($templateUuid, $category->getId());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);

        $this->deactivateTemplate($templateUuid);

        $this->get(CategoryTemplateAttributeSaver::class)->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection()
        );

        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateModel->getUuid());

        $this->assertEquals(AttributeCollection::fromArray([]), $insertedAttributes);
    }
}
