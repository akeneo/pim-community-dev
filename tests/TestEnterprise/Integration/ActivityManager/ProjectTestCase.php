<?php

namespace TestEnterprise\Integration\ActivityManager;


class ProjectTestCase extends ActivityManagerTestCase
{
    public function testProjectCreation()
    {
        $projectFactory = $this->get('pimee_activity_manager.factory.project');
        $entityManager = $this->get('doctrine.orm.default_entity_manager');
        $attributeGroupRepository = $this->get('pim_catalog.repository.attribute_group');
        $productRepository = $this->get('pim_catalog.repository.product');
        $localeRepository = $this->get('pim_catalog.repository.locale');
        $channelRepository = $this->get('pim_catalog.repository.channel');

        $projectData = [
            'label' => 'test-project',
            'description' => 'An awesome description',
            'due_date' => '2020-01-19',
            'datagrid_view' =>
                ['filters' => '', 'columns' => 'sku,label,family'],
            'locale' => 'en_US',
            'owner'=> 'admin',
            'channel' => 'ecommerce',
            'product_filters' =>
                [
                    [
                        'field' => 'sku',
                        'operator' => '=',
                        'value' => 'tshirt-the-witcher-3',
                        'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
                    ],
                ],
        ];

        $project = $projectFactory->create($projectData);

        $this->get('pimee_activity_manager.saver.project')->save($project);

        $this->get('pimee_activity_manager.launcher.job.project_calculation')->launch($project);

        $completenessPerAttributeGroup = $entityManager->getConnection()->fetchAssoc(
            'SELECT * FROM pimee_activity_manager_completeness_per_attribute_group'
        );

        var_dump($completenessPerAttributeGroup);

        $productId = $productRepository->findOneByIdentifier('tshirt-the-witcher-3')->getId();
        $marketingId = $attributeGroupRepository->findOneByIdentifier('marketing')->getId();
        $technicalId = $attributeGroupRepository->findOneByIdentifier('technical')->getId();
        $otherId = $attributeGroupRepository->findOneByIdentifier('other')->getId();

        $channelId = $channelRepository->findOneByIdentifier('ecommerce')->getId();
        $localeId = $localeRepository->findOneByIdentifier('en_US')->getId();

        foreach ($completenessPerAttributeGroup as $result) {
            $this->assertSame($localeId, $result[0][0]);
            $this->assertSame($channelId, $result[0][1]);
            $this->assertSame($productId, $result[0][2]);
            $this->assertSame($marketingId, $result[0][3]);
        }
    }
}
