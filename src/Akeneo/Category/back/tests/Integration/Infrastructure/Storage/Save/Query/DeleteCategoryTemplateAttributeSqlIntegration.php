<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\DeleteTemplateAttribute;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;

class DeleteCategoryTemplateAttributeSqlIntegration extends CategoryTestCase
{
    public function testDeleteAttributeFromDatabase(): void
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

        /** @var AttributeCollection $insertedAttributes */
        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateModel->getUuid());

        $this->assertCount(13, $insertedAttributes);
        foreach(range(0,2) as $index) {
            $attributeUuid = $insertedAttributes->getAttributes()[$index]->getUuid();
            ($this->get(DeleteTemplateAttribute::class))($templateModel->getUuid(), $attributeUuid);
        }
        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateModel->getUuid());
        $this->assertCount(10, $insertedAttributes);
    }

    public function testItDoesNotDeleteCategoryAttributeOnDeactivatedTemplate(): void
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

        $this->deactivateTemplate($templateUuid);

        /** @var AttributeCollection $insertedAttributes */
        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateModel->getUuid());

        $this->assertCount(13, $insertedAttributes);
        foreach(range(0,2) as $index) {
            $attributeUuid = $insertedAttributes->getAttributes()[$index]->getUuid();
            ($this->get(DeleteTemplateAttribute::class))($templateModel->getUuid(), $attributeUuid);
        }
        $insertedAttributes = $this->get(GetAttribute::class)->byTemplateUuid($templateModel->getUuid());
        $this->assertCount(13, $insertedAttributes);
    }
}
