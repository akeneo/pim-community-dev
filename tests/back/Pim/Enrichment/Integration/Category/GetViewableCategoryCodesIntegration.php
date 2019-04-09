<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Category;

use Akeneo\Pim\Enrichment\Component\Category\Query\GetViewableCategoryCodesInterface;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\CategoryTree\CategoryTreeFixturesLoader;

/**
 * @author    Anaël CHARDAN <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetViewableCategoryCodesIntegration extends TestCase
{
    /** @var CategoryTreeFixturesLoader */
    private $fixturesLoader;
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixturesLoader = new CategoryTreeFixturesLoader($this->testKernel->getContainer());
    }

    public function testGetAllCategories(): void
    {
        $this->fixturesLoader->givenTheCategoryTrees([
            'master_category' => ['subcategory' => []],
            'another_master_category' => []
        ]);

        $expected = ['master_category', 'subcategory', 'another_master_category'];
        $actual = $this->getQuery()->getViewableCategoryCodes(5, $expected);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetNoCategories(): void
    {
        $expected = [];
        $actual = $this->getQuery()->getViewableCategoryCodes(5, []);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function getQuery(): GetViewableCategoryCodesInterface
    {
        return $this->testKernel->getContainer()->get('akeneo.pim.enrichment.category.get_viewable_category_codes');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
