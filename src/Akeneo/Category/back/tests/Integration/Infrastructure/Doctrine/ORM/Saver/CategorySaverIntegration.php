<?php

namespace Akeneo\Test\Category\Integration\Infrastructure\Doctrine\ORM\Saver;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Normalizer\Standard\CategoryNormalizer;
use Akeneo\Category\Infrastructure\Doctrine\ORM\Saver\CategorySaver;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedCategoryCleaner;
use PHPUnit\Framework\Assert;

class CategorySaverIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_that_it_saves_a_new_category(): void
    {
        $category = $this->createCategoryWithoutSaving([
            'code' => 'foo',
        ]);
        $createdNormalizedCategory = $this->getCategoryNormalizer()->normalize($category);

        $this->getCategorySaver()->save($category);

        $persistedCategory = $this->getCategoryRepository()->findOneByIdentifier('foo');
        $persistedNormalizedCategory = $this->getCategoryNormalizer()->normalize($persistedCategory);

        NormalizedCategoryCleaner::clean($createdNormalizedCategory);
        NormalizedCategoryCleaner::clean($persistedNormalizedCategory);

        self::assertEquals($createdNormalizedCategory, $persistedNormalizedCategory);
    }

    public function test_that_it_saves_a_new_sub_category(): void
    {
        $parent = $this->createCategoryWithoutSaving([
            'code' => 'foo',
        ]);
        $this->getCategorySaver()->save($parent);

        $category = $this->createCategoryWithoutSaving([
            'code' => 'bar',
            'parent' => 'foo',
        ]);
        $createdNormalizedCategory = $this->getCategoryNormalizer()->normalize($category);

        $this->getCategorySaver()->save($category);

        $persistedCategory = $this->getCategoryRepository()->findOneByIdentifier('bar');
        $persistedNormalizedCategory = $this->getCategoryNormalizer()->normalize($persistedCategory);

        NormalizedCategoryCleaner::clean($createdNormalizedCategory);
        NormalizedCategoryCleaner::clean($persistedNormalizedCategory);

        self::assertEquals($createdNormalizedCategory, $persistedNormalizedCategory);
    }

    /**
     * Move the 'jeans' category from 'master' to 'clothes'.
     * Before:
     *  - master
     *      - jeans
     * After:
     *  - master
     *      - clothes
     *          - jeans
     */
    public function test_it_set_the_updated_property_when_the_category_is_moved_and_the_parent_change(): void
    {
        /** @var Category */
        $jeans = $this->createCategory([
            'code' => 'jeans',
            'parent' => 'master'
        ]);

        // Set the updated property to timestamp 0.
        $jeans->setUpdated(new \DateTime('@0'));
        $this->getCategorySaver()->save($jeans);

        $this->createCategory([
            'code' => 'clothes',
        ]);

        $this->updateCategory($jeans, [
            'parent' => 'clothes'
        ]);

        Assert::assertGreaterThan(
            0,
            $jeans->getUpdated()->getTimestamp()
        );
    }

    /**
     * Move the parent ('pants') of 'jeans' category from 'master' to 'clothes'.
     * Before:
     *  - master
     *      - pants
     *          -jeans
     * After:
     *  - master
     *      - clothes
     *          - pants
     *              -jeans
     */
    public function test_it_doesnt_set_the_updated_property_when_the_parent_category_is_moved(): void
    {
        $pants = $this->createCategory([
            'code' => 'pants',
            'parent' => 'master',
        ]);

        /** @var Category */
        $jeans = $this->createCategory([
            'code' => 'jeans',
            'parent' => 'pants',
        ]);

        // Set the updated property to timestamp 0.
        $jeans->setUpdated(new \DateTime('@0'));
        $this->getCategorySaver()->save($jeans);

        $this->createCategory([
            'code' => 'clothes',
            'parent' => 'master',
        ]);

        $this->updateCategory($pants, [
            'parent' => 'clothes'
        ]);

        Assert::assertEquals(
            0,
            $jeans->getUpdated()->getTimestamp()
        );
    }

    /**
     * Inverse order between 'jeans' and 'shoes' categories.
     * Before:
     *  - master
     *      - jeans
     *      - shoes
     * After:
     *  - master
     *      - shoes
     *      - jeans
     */
    public function test_it_doesnt_set_the_updated_property_when_the_category_is_reordered_among_its_siblings(): void
    {
        /** @var Category */
        $jeans = $this->createCategory([
            'code' => 'jeans',
            'parent' => 'master',
        ]);

        // Set the updated property to timestamp 0.
        $jeans->setUpdated(new \DateTime('@0'));
        $this->getCategorySaver()->save($jeans);

        /** @var Category */
        $shoes = $this->createCategoryWithoutSaving([
            'code' => 'shoes',
            'parent' => 'master',
        ]);
        $this->getCategoryRepository()->persistAsNextSiblingOf($shoes, $jeans);
        $this->getCategorySaver()->save($shoes);

        // Set the updated property to timestamp 0.
        $shoes->setUpdated(new \DateTime('@0'));
        $this->getCategorySaver()->save($shoes);

        $this->getCategoryRepository()->persistAsNextSiblingOf($jeans, $shoes);
        $this->getCategorySaver()->save($jeans);

        Assert::assertEquals(
            0,
            $jeans->getUpdated()->getTimestamp()
        );

        Assert::assertEquals(
            0,
            $shoes->getUpdated()->getTimestamp()
        );
    }

    public function test_it_set_the_updated_property_when_a_translation_is_updated(): void
    {
        /** @var Category */
        $master = $this->getCategoryRepository()->findOneByIdentifier('master');

        // Set the updated property to timestamp 0.
        $master->setUpdated(new \DateTime('@0'));
        $this->getCategorySaver()->save($master);

        $this->updateCategory($master, [
            'labels' => [
                'en_US' => 'Master2'
            ]
        ]);

        Assert::assertGreaterThan(
            0,
            $master->getUpdated()->getTimestamp()
        );
    }

    /**
     * @param array<mixed> $data
     */
    private function createCategoryWithoutSaving(array $data = []): CategoryInterface
    {
        $category = $this->get('pim_catalog.factory.category')->create();
        $this->get('pim_catalog.updater.category')->update($category, $data);
        $this->get('validator')->validate($category);

        return $category;
    }

    /**
     * @param array<mixed> $data
     */
    private function updateCategory(CategoryInterface $category, array $data = []): CategoryInterface
    {
        $this->get('pim_catalog.updater.category')->update($category, $data);
        $this->get('validator')->validate($category);
        $this->getCategorySaver()->save($category);

        return $category;
    }

    private function getCategorySaver(): CategorySaver
    {
        return $this->get('pim_catalog.saver.category');
    }

    private function getCategoryNormalizer(): CategoryNormalizer
    {
        return $this->get('pim_catalog.normalizer.standard.category');
    }

    private function getCategoryRepository(): CategoryRepositoryInterface
    {
        return $this->get('pim_catalog.repository.category');
    }
}
