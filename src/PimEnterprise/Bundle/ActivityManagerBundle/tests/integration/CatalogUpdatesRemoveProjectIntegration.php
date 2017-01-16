<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;

class CatalogUpdatesRemoveProjectIntegration extends ActivityManagerTestCase
{
    /**
     * A project has to be removed if its channel is removed.
     */
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
        $this->assertTrue(null === $result, 'Project not removed after its channel has been removed.');
    }

    /**
     * A project has to be removed if its locale is now deactivated or if its locale is no longer part
     * of its channel locales.
     */
    public function testDeactivateLocaleRemovesAssociatedProjects()
    {
        $localeRepository = $this->get('pim_catalog.repository.locale');
        $channelRepository = $this->get('pim_catalog.repository.channel');
        $channelSaver = $this->get('pim_catalog.saver.channel');
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

        $locale = $localeRepository->findOneByIdentifier('en_US');
        $channel = $channelRepository->findOneByIdentifier('mobile');
        $channel->removeLocale($locale);
        $channelSaver->save($channel);

        $result = $projectRepository->findOneByIdentifier($projectCode);
        $this->assertTrue(null === $result, 'Project not removed after its locale has been deactivated.');
    }

    /**
     * A project must be removed if an attribute used as product filter is removed.
     */
    public function testRemoveAttributeRemovesAssociatedProjects()
    {
        $attributeRemover = $this->get('pim_catalog.remover.attribute');
        $attributeRepository = $this->get('pim_catalog.repository.attribute');
        $projectRepository = $this->get('pimee_activity_manager.repository.project');
        $project = $this->createProject(
            [
                'label'           => 'High-Tech project',
                'locale'          => 'en_US',
                'owner'           => 'admin',
                'channel'         => 'mobile',
                'product_filters' => [
                    [
                        'field'    => 'release_date',
                        'operator' => '=',
                        'value'    => '2016-08-13',
                        'context'  => ['locale' => 'en_US', 'scope' => 'mobile'],
                    ],
                ],
            ]
        );
        $this->calculateProject($project);
        $projectCode = $project->getCode();

        $attribute = $attributeRepository->findOneByIdentifier('release_date');
        $attributeRemover->remove($attribute);

        $result = $projectRepository->findOneByIdentifier($projectCode);
        $this->assertTrue(
            null === $result,
            'Project not removed after an attribute used in product filters has been removed.'
        );
    }

    /**
     * A project must be removed if a category used as product filter is removed.
     */
    public function testRemoveCategoryRemovesAssociatedProjects()
    {
        $categoryRemover = $this->get('pim_catalog.remover.category');
        $categoryRepository = $this->get('pim_catalog.repository.category');
        $projectRepository = $this->get('pimee_activity_manager.repository.project');
        $project = $this->createProject(
            [
                'label'           => 'High-Tech project',
                'locale'          => 'en_US',
                'owner'           => 'admin',
                'channel'         => 'mobile',
                'product_filters' => [
                    [
                        'field'    => 'categories',
                        'operator' => 'IN',
                        'value'    => ['clothing'],
                        'context'  => ['locale' => 'en_US', 'scope' => 'mobile'],
                    ],
                ],
            ]
        );
        $this->calculateProject($project);
        $projectCode = $project->getCode();

        $category = $categoryRepository->findOneByIdentifier('clothing');
        $categoryRemover->remove($category);

        $result = $projectRepository->findOneByIdentifier($projectCode);
        $this->assertTrue(
            null === $result,
            'Project not removed after a category used in product filters has been removed.'
        );
    }

    /**
     * A project must be removed if an attribute used as product filter is removed.
     */
    public function testRemoveCurrencyFromChannelRemovesAssociatedProjects()
    {
        $channelRepository = $this->get('pim_catalog.repository.channel');
        $projectRepository = $this->get('pimee_activity_manager.repository.project');
        $currencyRepository = $this->get('pim_catalog.repository.currency');
        $channelSaver = $this->get('pim_catalog.saver.channel');
        $project = $this->createProject(
            [
                'label'           => 'High-Tech project',
                'locale'          => 'fr_FR',
                'owner'           => 'admin',
                'channel'         => 'ecommerce',
                'product_filters' => [
                    [
                        'field'    => 'price_attribute',
                        'operator' => '>',
                        'value'    => ['amount' => 30, 'currency' => 'EUR'],
                        'context'  => ['locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ],
                ],
            ]
        );
        $this->calculateProject($project);
        $projectCode = $project->getCode();

        $channel = $channelRepository->findOneByIdentifier('ecommerce');
        $currencyEUR = $currencyRepository->findOneByIdentifier('EUR');
        $channel->removeCurrency($currencyEUR);
        $channelSaver->save($channel);

        $result = $projectRepository->findOneByIdentifier($projectCode);
        $this->assertTrue(
            null === $result,
            'Project not removed after its channel removed a currency used in product filters.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        $rootPath = $this->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        return new Configuration(
            [$rootPath . 'tests' . DIRECTORY_SEPARATOR . 'catalog' .    DIRECTORY_SEPARATOR . 'activity_manager'],
            true
        );
    }
}
