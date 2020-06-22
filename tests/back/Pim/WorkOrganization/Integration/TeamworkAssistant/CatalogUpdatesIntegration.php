<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant;

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

        // @see https://github.com/akeneo/pim-community-dev/issues/10828
        // kill background process because you can have a race condition:
        // - this test triggers the asynchronous job pim:catalog:remove-completeness-for-channel-and-locale and then the test finishes (but not the job)
        // - then table are cleaned in the next test with the fixture loader
        // - then the pim:catalog:remove-completeness-for-channel-and-locale insert data into a table
        // - then the dump is loaded to load the fixtures of the next test
        // - INSERT INTO of this dump fails because the data inserted by "pim:catalog:remove-completeness-for-channel-and-locale" already exists
        //
        // ideally, we should not trigger this asynchronous job and test it differently
        exec('pkill -f "pim:catalog:remove-completeness-for-channel-and-locale"');
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
     * A project should not be impacted when a category not used as product filter is removed.
     */
    public function testRemoveExternalCategory()
    {
        $categoryRemover = $this->get('pim_catalog.remover.category');
        $categoryRepository = $this->get('pim_catalog.repository.category');
        $project = $this->createProject('High-Tech project', 'admin', 'en_US', 'tablet', [
            [
                'field'    => 'categories',
                'operator' => 'IN OR UNCLASSIFIED',
                'value'    => [
                    'default',
                    'clothing',
                    'high_tech',
                    'decoration',
                    'car',
                ],
                'type' => 'field',
            ],
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => [
                    'clothing'
                ],
                'type' => 'field',
            ],
        ]);

        $category = $categoryRepository->findOneByIdentifier('car');
        $categoryRemover->remove($category);

        $this->calculateProject($project);
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
            [
                $rootPath .
                'tests' .
                DIRECTORY_SEPARATOR .
                'back' .
                DIRECTORY_SEPARATOR .
                'Integration' .
                DIRECTORY_SEPARATOR .
                'catalog' .
                DIRECTORY_SEPARATOR .
                'teamwork_assistant'
            ]
        );
    }
}
