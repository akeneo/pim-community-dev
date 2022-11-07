<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Classification\CategoryTree;
use Akeneo\Category\Domain\Query\GetCategoryTreesInterface;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface as CategoryDoctrine;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreesSqlIntegration extends TestCase
{
    private const TEMPLATE_UUID = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';

    private CategoryDoctrine|CategoryTree $categoryParent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->insertFixtures();
    }

    public function testGetAllCategoryTrees(): void
    {
        $categoryTrees = $this->get(GetCategoryTreesInterface::class)->getAll();
        $this->assertCount(2, $categoryTrees);
        $this->assertSame('categoryParent', (string) $categoryTrees[0]->getCode());
        $this->assertSame(self::TEMPLATE_UUID, (string) $categoryTrees[0]->getCategoryTreeTemplate()->getTemplateUuid());
        $this->assertSame([
            'en_US' => 'The template',
            'fr_FR' => 'Le modèle'
        ],  $categoryTrees[0]->getCategoryTreeTemplate()->getTemplateLabels()->normalize());
        $this->assertSame('master', (string) $categoryTrees[1]->getCode());
    }

    public function testGetCategoryTreesByIds(): void
    {
        $categoryTrees = $this->get(GetCategoryTreesInterface::class)->byIds([$this->categoryParent->getId()]);
        $this->assertSame('categoryParent', (string) $categoryTrees[0]->getCode());
        $this->assertSame(self::TEMPLATE_UUID, (string) $categoryTrees[0]->getCategoryTreeTemplate()->getTemplateUuid());
        $this->assertSame([
            'en_US' => 'The template',
            'fr_FR' => 'Le modèle'
        ],  $categoryTrees[0]->getCategoryTreeTemplate()->getTemplateLabels()->normalize());
    }

    private function insertFixtures(): void
    {
        $this->categoryParent = $this->createCategory(['code' => 'categoryParent']);
        $this->createCategory(['code' => 'categoryChild', 'parent' => 'categoryParent']);

        $templateUuid = TemplateUuid::fromString(self::TEMPLATE_UUID);

        $sqlInsertTemplate = <<<SQL
            INSERT INTO pim_catalog_category_template (uuid, code, labels)
            VALUES (:uuid, 'the_template', :labels);
        SQL;
        $this->get('database_connection')->executeQuery($sqlInsertTemplate, [
            'uuid' => $templateUuid->toBytes(),
            'labels' => json_encode([
                'en_US' => 'The template',
                'fr_FR' => 'Le modèle'
            ])
        ]);

        $sqlInsertLinkTemplateToCategory = <<<SQL
            INSERT INTO pim_catalog_category_tree_template (category_tree_id, category_template_uuid)
            VALUES (:category_tree_id, :template_uuid);
        SQL;
        $this->get('database_connection')->executeQuery($sqlInsertLinkTemplateToCategory, [
            'category_tree_id' => $this->categoryParent->getId(),
            'template_uuid' => $templateUuid->toBytes()
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
