<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\ActivateTemplate;
use Akeneo\Category\Application\Query\DeleteTemplateAndAttributes;
use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\DeleteCategoryTreeTemplateByCategoryIdAndTemplateUuid;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteTemplateAndAttributesSqlIntegration extends CategoryTestCase
{
    public function testDeleteTemplateAndAttributes(): void
    {
        $category = $this->insertBaseCategory(new Code('template_deletion'));

        $activateTemplateService = $this->get(ActivateTemplate::class);
        $templateModel = $this->generateMockedCategoryTemplateModel(
            categoryTreeId: $category->getId()->getValue()
        );

        // Create template with dedicated service
        $templateUuid = ($activateTemplateService)(
            $templateModel->getCategoryTreeId(),
            $templateModel->getCode(),
            $templateModel->getLabelCollection()
        );

        /** @var Template $template */
        $template = $this->get(GetTemplate::class)->byUuid($templateUuid);
        self::assertNotNull($template);
        self::assertEquals($templateUuid, $template->getUuid()->getValue());

        // Call DeleteCategoryTreeTemplate to remove association with pim_catalog_category_tree_template before removing template
        $deleteCategoryTreeTemplateService = $this->get(DeleteCategoryTreeTemplateByCategoryIdAndTemplateUuid::class);
        ($deleteCategoryTreeTemplateService)($category->getId(), $templateUuid);
        $deleteTemplateAndAttributesService = $this->get(DeleteTemplateAndAttributes::class);
        ($deleteTemplateAndAttributesService)($templateUuid);
        $template = $this->get(GetTemplate::class)->byUuid($templateUuid);
        self::assertNull($template);
    }

    public function testDeleteTemplateAndAttributesWithoutTemplateUuid(): void
    {
        // Non existing template in base
        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');

        /** @var Template $template */
        $template = $this->get(GetTemplate::class)->byUuid($templateUuid);
        self::assertNull($template);

        $deleteTemplateAndAttributesService = $this->get(DeleteTemplateAndAttributes::class);
        ($deleteTemplateAndAttributesService)($templateUuid);
        $template = $this->get(GetTemplate::class)->byUuid($templateUuid);
        self::assertNull($template);
    }
}
