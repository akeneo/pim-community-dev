<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\CategorySaver;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\Normalizer\Standard\CategoryNormalizer;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;

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

        self::assertEquals($createdNormalizedCategory, $persistedNormalizedCategory);
    }

    private function createCategoryWithoutSaving(array $data = []): CategoryInterface
    {
        $category = $this->get('pim_catalog.factory.category')->create();
        $this->get('pim_catalog.updater.category')->update($category, $data);
        $this->get('validator')->validate($category);

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
