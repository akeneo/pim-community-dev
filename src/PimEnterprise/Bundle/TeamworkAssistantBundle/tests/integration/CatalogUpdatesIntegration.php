<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;

class CatalogUpdatesIntegration extends TeamworkAssistantTestCase
{
    /**
     * A project has to be removed if its channel is removed.
     */
    public function testRemoveChannelRemovesAssociatedProjects()
    {
        $channelRemover = $this->get('pim_catalog.remover.channel');
        $channelRepository = $this->get('pim_catalog.repository.channel');
        $projectRepository = $this->get('pimee_teamwork_assistant.repository.project');
        $project = $this->createProject('High-Tech project', 'admin', 'en_US', 'tablet', [
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['high_tech'],
            ],
        ]);
        $projectCode = $project->getCode();

        $tabletChannel = $channelRepository->findOneByIdentifier('tablet');
        $channelRemover->remove($tabletChannel);

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
        $projectRepository = $this->get('pimee_teamwork_assistant.repository.project');
        $project = $this->createProject('High-Tech project', 'admin', 'en_US', 'tablet', [
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['high_tech'],
            ],
        ]);

        $projectCode = $project->getCode();

        $locale = $localeRepository->findOneByIdentifier('en_US');
        $channel = $channelRepository->findOneByIdentifier('tablet');
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
        $projectRepository = $this->get('pimee_teamwork_assistant.repository.project');
        $project = $this->createProject('High-Tech project', 'admin', 'en_US', 'tablet', [
            [
                'field'    => 'release_date',
                'operator' => '=',
                'value'    => '2016-08-13',
            ],
        ]);

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
        $projectRepository = $this->get('pimee_teamwork_assistant.repository.project');
        $project = $this->createProject('High-Tech project', 'admin', 'en_US', 'tablet', [
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['clothing'],
            ],
        ]);

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
        $projectRepository = $this->get('pimee_teamwork_assistant.repository.project');
        $currencyRepository = $this->get('pim_catalog.repository.currency');
        $channelSaver = $this->get('pim_catalog.saver.channel');
        $project = $this->createProject('High-Tech project', 'admin', 'fr_FR', 'ecommerce', [
            [
                'field'    => 'price_attribute',
                'operator' => '>',
                'value'    => ['amount' => 30, 'currency' => 'EUR'],
            ],
        ]);

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
            [$rootPath . 'tests' . DIRECTORY_SEPARATOR . 'catalog' .    DIRECTORY_SEPARATOR . 'teamwork_assistant']
        );
    }
}
