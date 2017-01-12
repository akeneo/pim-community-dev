<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TestEnterprise\Integration\ActivityManager;

class CatalogUpdatesRemoveProjectIntegration extends ActivityManagerTestCase
{
    public function testRemoveChannelRemovesAssociatedProjects()
    {
        $channelRemover = $this->get('pim_catalog.remover.channel');
        $channelRepository = $this->get('pim_catalog.repository.channel');
        $projectRepository = $this->get('pimee_activity_manager.repository.project');
        $project = $this->createProject([
            'label' => 'High-Tech project',
            'locale' => 'en_US',
            'owner'=> 'admin',
            'channel' => 'mobile',
            'product_filters' =>[
                [
                    'field' => 'categories',
                    'operator' => 'IN',
                    'value' => ['high_tech'],
                    'context' => ['locale' => 'en_US', 'scope' => 'mobile'],
                ],
            ],
        ]);
        $this->calculateProject($project);
        $projectCode = $project->getCode();

        $mobileChannel = $channelRepository->findOneByIdentifier('mobile');
        $channelRemover->remove($mobileChannel);

        $result = $projectRepository->findOneByIdentifier($projectCode);
        $this->assertNull($result);
    }
}
