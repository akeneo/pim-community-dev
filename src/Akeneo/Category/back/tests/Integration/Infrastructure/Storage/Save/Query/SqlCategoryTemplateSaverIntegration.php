<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\Query\GetTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

class SqlCategoryTemplateSaverIntegration extends CategoryTestCase
{
    public function testInsertNewCategoryTemplate(): void
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

        $this->assertEquals($templateModel->getCode(),$insertedTemplate->getCode());
        $this->assertEquals($templateModel->getLabelCollection(),$insertedTemplate->getLabelCollection());
    }

    public function testUpdatesCategoryTemplate(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');

        // Given

        $template = new Template(
            TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            new TemplateCode('default_template'),
            LabelCollection::fromArray(['en_US' => 'Default template', 'fr_FR' => 'Template par défaut']),
            $category->getId(),
            null,
        );

        $this->get(CategoryTemplateSaver::class)->insert($template);
        $this->get(CategoryTreeTemplateSaver::class)->insert($template);

        // When

        $template = new Template(
            TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            new TemplateCode('default_template'),
            LabelCollection::fromArray(['en_US' => 'Default template']),
            $category->getId(),
            null,
        );

        $this->get(CategoryTemplateSaver::class)->update($template);

        // Then

        $expectedTemplate = new Template(
            TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            new TemplateCode('default_template'),
            LabelCollection::fromArray(['en_US' => 'Default template']),
            $category->getId(),
            AttributeCollection::fromArray([]),
        );

        /** @var Template $updatedTemplate */
        $updatedTemplate = $this->get(GetTemplate::class)->byUuid($template->getUuid());

        $this->assertEquals($expectedTemplate, $updatedTemplate);
    }

    public function testUpdatesCategoryTemplateDoesntUpdateReadonlyProperties(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');

        // Given

        $template = new Template(
            TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            new TemplateCode('readonly_code'),
            LabelCollection::fromArray([]),
            $category->getId(),
            null
        );

        $this->get(CategoryTemplateSaver::class)->insert($template);
        $this->get(CategoryTreeTemplateSaver::class)->insert($template);

        // When

        $template = new Template(
            TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            new TemplateCode('new_code'),
            LabelCollection::fromArray([]),
            new CategoryId(9999),
            null,
        );

        $this->get(CategoryTemplateSaver::class)->update($template);

        // Then

        $expectedTemplate = new Template(
            TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            new TemplateCode('readonly_code'),
            LabelCollection::fromArray([]),
            $category->getId(),
            AttributeCollection::fromArray([])
        );

        /** @var Template $updatedTemplate */
        $updatedTemplate = $this->get(GetTemplate::class)->byUuid($template->getUuid());

        $this->assertEquals($expectedTemplate, $updatedTemplate);
    }

    public function testUpdatesCategoryTemplateIgnoreUnknownTemplate(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');

        // When

        $template = new Template(
            TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            new TemplateCode('default_template'),
            LabelCollection::fromArray(['en_US' => 'Default template']),
            $category->getId(),
            null,
        );

        $this->get(CategoryTemplateSaver::class)->update($template);

        // Then

        $this->doesNotPerformAssertions();
    }
}
