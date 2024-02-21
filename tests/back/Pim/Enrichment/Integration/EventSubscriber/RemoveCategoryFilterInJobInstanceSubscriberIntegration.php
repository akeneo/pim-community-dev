<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\tests\integration\EventListener;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\ORM\EntityManager;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveCategoryFilterInJobInstanceSubscriberIntegration extends TestCase
{
    public function testValueFilterIsDeletedInJobInstance()
    {
        $category = $this->createCategory(['code' => 'foo']);
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier(
            $category->getCode()
        );
        $jobInstance = $this->createJobInstanceWithCategoryFilter(
            'job1',
            ['master_accessories_scarves', $category->getCode(), 'whatever']
        );

        $this->get('pim_catalog.remover.category')->remove($this->get('pim_catalog.repository.category')->findOneByIdentifier($category->getCode()));

        $jobInstance = $this
            ->get('akeneo_batch.job.job_instance_repository')
            ->findOneByIdentifier($jobInstance->getCode());
        $rawParameters = $jobInstance->getRawParameters();
        $filters = array_filter($rawParameters['filters']['data'] ?? [], function ($filter) {
            return 'categories' === $filter['field'];
        });
        $this->assertCount(1, $filters);
        $categoryFilter = current($filters);
        $this->assertEquals(['master_accessories_scarves', 'whatever'], $categoryFilter['value']);
    }

    public function testEntireFilterIsDeletedInJobInstance()
    {
        $category = $this->createCategory(['code' => 'bar']);
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier(
            $category->getCode()
        );
        $jobInstance = $this->createJobInstanceWithCategoryFilter(
            'job2',
            [$category->getCode()]
        );

        $this->get('pim_catalog.remover.category')->remove($this->get('pim_catalog.repository.category')->findOneByIdentifier($category->getCode()));

        $jobInstance = $this
            ->get('akeneo_batch.job.job_instance_repository')
            ->findOneByIdentifier($jobInstance->getCode());
        $rawParameters = $jobInstance->getRawParameters();
        $filters = array_filter($rawParameters['filters']['data'] ?? [], function ($filter) {
            return 'categories' === $filter['field'];
        });
        $this->assertCount(1, $filters);
        $categoryFilter = current($filters);
        $this->assertEquals(['bar'], $categoryFilter['value']);
    }

    public function testValueFilterIsDeletedInJobInstanceWhenParentIsDeleted()
    {
        $parentCategory = $this->createCategory(['code' => 'parent']);
        $category = $this->createCategory(['code' => 'foobar', 'parent' => 'parent']);
        $jobInstance = $this->createJobInstanceWithCategoryFilter(
            'job3',
            [$category->getCode()]
        );

        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $this->get('pim_catalog.remover.category')->remove($this->get('pim_catalog.repository.category')->findOneByIdentifier($parentCategory->getCode()));
        $this->assertNull($this->get('pim_catalog.repository.category')->findOneByIdentifier(
            $category->getCode()
        ));

        $jobInstance = $this
            ->get('akeneo_batch.job.job_instance_repository')
            ->findOneByIdentifier($jobInstance->getCode());
        $rawParameters = $jobInstance->getRawParameters();
        $filters = array_filter($rawParameters['filters']['data'] ?? [], function ($filter) {
            return 'categories' === $filter['field'];
        });
        $this->assertCount(1, $filters);
        $categoryFilter = current($filters);
        $this->assertEquals(['parent'], $categoryFilter['value']);
    }

    public function testValuesFilterAreDeletedInJobInstance()
    {
        $parentCategory = $this->createCategory(['code' => 'parent']);
        $category1 = $this->createCategory(['code' => 'cat1', 'parent' => 'parent']);
        $category2 = $this->createCategory(['code' => 'cat2', 'parent' => 'parent']);
        $category3 = $this->createCategory(['code' => 'cat3']);
        $jobInstance = $this->createJobInstanceWithCategoryFilter(
            'job3',
            ['master_accessories_scarves', $category1->getCode(), $category2->getCode(), $category3->getCode(), 'what']
        );

        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $this->get('pim_catalog.remover.category')->removeAll([
            $this->get('pim_catalog.repository.category')->findOneByIdentifier($parentCategory->getCode()),
            $this->get('pim_catalog.repository.category')->findOneByIdentifier($category3->getCode()),
        ]);

        $jobInstance = $this
            ->get('akeneo_batch.job.job_instance_repository')
            ->findOneByIdentifier($jobInstance->getCode());
        $rawParameters = $jobInstance->getRawParameters();
        $filters = array_filter($rawParameters['filters']['data'] ?? [], function ($filter) {
            return 'categories' === $filter['field'];
        });
        $this->assertCount(1, $filters);
        $categoryFilter = current($filters);
        $this->assertEquals(['master_accessories_scarves', 'what'], $categoryFilter['value']);
    }

    private function createJobInstanceWithCategoryFilter(string $code, array $values = []): JobInstance
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.default_entity_manager');

        $jobInstance = new JobInstance('connector', 'type', 'job_name');
        $jobInstance->setCode($code);
        $jobInstance->setLabel($code);
        $jobInstance->setRawParameters([
            'filters' => [
                'data' => [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true
                    ],
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => $values,
                    ],
                ],
            ],
        ]);
        $entityManager->persist($jobInstance);
        $entityManager->flush();

        return $jobInstance;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
