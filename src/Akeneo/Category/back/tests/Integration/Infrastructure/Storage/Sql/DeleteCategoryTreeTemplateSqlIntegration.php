<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\DeleteCategoryTreeTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Test\Integration\Configuration;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCategoryTreeTemplateSqlIntegration extends CategoryTestCase
{
    public function testItDeletesLinkBetweenCategoryTreeAndTemplate(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');
        $templateModel = $this->generateMockedCategoryTemplateModel(categoryTreeId: $category->getId()->getValue());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);

        $this->assertTrue($this->isExistingLinkBetweenCategoryTreeAndTemplate($category->getId()->getValue(), $templateModel->getUuid()));

        $this->get(DeleteCategoryTreeTemplate::class)->byCategoryIdAndTemplateUuid($category->getId(), $templateModel->getUuid());

        $this->assertFalse($this->isExistingLinkBetweenCategoryTreeAndTemplate($category->getId()->getValue(), $templateModel->getUuid()));
    }

    public function testItDeletesLinkForTemplate(): void
    {
        /** @var Category $category */
        $category = $this->get(GetCategoryInterface::class)->byCode('master');
        $templateModel = $this->generateMockedCategoryTemplateModel(categoryTreeId: $category->getId()->getValue());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);

        $this->assertTrue($this->isExistingLinkBetweenCategoryTreeAndTemplate($category->getId()->getValue(), $templateModel->getUuid()));

        $this->get(DeleteCategoryTreeTemplate::class)->byTemplateUuid($templateModel->getUuid());

        $this->assertFalse($this->isExistingLinkBetweenCategoryTreeAndTemplate($category->getId()->getValue(), $templateModel->getUuid()));
    }

    private function isExistingLinkBetweenCategoryTreeAndTemplate(int $categoryTreeId, TemplateUuid $templateUuid): bool
    {
        $query = <<< SQL
            SELECT COUNT(*) 
            FROM pim_catalog_category_tree_template
            WHERE category_tree_id = :category_tree_id
            AND category_template_uuid = :template_uuid
        SQL;

        $result = $this->get('database_connection')->executeQuery(
            $query,
            [
                'category_tree_id' => $categoryTreeId,
                'template_uuid' => $templateUuid->toBytes(),
            ],
            [
                'category_tree_id' => \PDO::PARAM_INT,
                'template_uuid' => \PDO::PARAM_STR,
            ],
        )->fetchOne();

        return $result === '1';
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
