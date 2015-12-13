<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\InstallerBundle\Command;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\LocaleAccessManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Gives all rights to all users
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class GiveAllRightsToAllUsersCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:installer:grant-user-accesses')
            ->setDescription('Grant all rights to users')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $group = $this->getGroupRepository()->findOneByIdentifier('All');

        $this->setRightsOnCategories($group);
        $this->setRightsOnLocales($group);
        $this->setRightsOnAttributesGroups($group);
        $this->setRightsOnJobProfiles($group);

        $output->writeln('All rights have been given to all users');
    }

    /**
     * @param mixed $group
     */
    protected function setRightsOnCategories($group)
    {
        $catalogCategories = $this->getCatalogCategoryRepository()->findBy(['parent' => null]);
        $assetCategories = $this->getAssetCategoryRepository()->findBy(['parent' => null]);

        foreach ($catalogCategories as $category) {
            $categoryManager = $this->getAccessCategoryManager();
            $categoryManager->setAccess($category, [$group], [$group], [$group], true);
            $categoryManager->updateChildrenAccesses($category, [$group], [$group], [$group], [], [], []);
        }

        foreach ($assetCategories as $category) {
            $categoryManager = $this->getAccessCategoryAssetManager();
            $categoryManager->setAccess($category, [$group], [$group], [$group], true);
            $categoryManager->updateChildrenAccesses($category, [$group], [$group], [$group], [], [], []);
        }
    }

    /**
     * @param mixed $group
     */
    protected function setRightsOnLocales($group)
    {
        $locales = $this->getLocaleRepository()->findAll();

        foreach ($locales as $locale) {
            $this->getLocaleAccessManager()->setAccess($locale, [$group], [$group]);
        }
    }

    /**
     * @param $group
     */
    protected function setRightsOnAttributesGroups($group)
    {
        $attributesGroups = $this->geAttributeGroupRepository()->findAll();

        foreach ($attributesGroups as $attributesGroup) {
            $this->getAccessAttributeGroupManager()->setAccess($attributesGroup, [$group], [$group]);
        }
    }

    /**
     * @param $group
     */
    protected function setRightsOnJobProfiles($group)
    {
        $jobInstances = $this->getJobInstanceRepository()->findAll();

        foreach ($jobInstances as $jobInstance) {
            $this->getJobProfileAccessManager()->setAccess($jobInstance, [$group], [$group]);
        }
    }

    /**
     * @return CategoryAccessManager
     */
    protected function getAccessCategoryManager()
    {
        return $this->getContainer()->get('pimee_security.manager.category_access');
    }

    /**
     * @return CategoryAccessManager
     */
    protected function getAccessCategoryAssetManager()
    {
        return $this->getContainer()->get('pimee_product_asset.manager.category_access');
    }

    /**
     * @return JobProfileAccessManager
     */
    protected function getJobProfileAccessManager()
    {
        return $this->getContainer()->get('pimee_security.manager.job_profile_access');
    }

    /**
     * @return AttributeGroupAccessManager
     */
    protected function getAccessAttributeGroupManager()
    {
        return $this->getContainer()->get('pimee_security.manager.attribute_group_access');
    }

    /**
     * @return LocaleAccessManager
     */
    protected function getLocaleAccessManager()
    {
        return $this->getContainer()->get('pimee_security.manager.locale_access');
    }

    /**
     * @return CategoryRepositoryInterface
     */
    protected function getCatalogCategoryRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.category');
    }

    /**
     * @return JobInstance
     */
    protected function getJobInstanceRepository()
    {
        return $this->getContainer()->get('pim_import_export.repository.job_instance');
    }

    /**
     * @return AttributeGroupInterface
     */
    protected function geAttributeGroupRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.attribute_group');
    }

    /**
     * @return CategoryRepositoryInterface
     */
    protected function getAssetCategoryRepository()
    {
        return $this->getContainer()->get('pimee_product_asset.repository.category');
    }

    /**
     * @return GroupRepositoryInterface
     */
    protected function getGroupRepository()
    {
        return $this->getContainer()->get('pim_user.repository.group');
    }

    /**
     * @return LocaleRepositoryInterface
     */
    protected function getLocaleRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.locale');
    }
}
