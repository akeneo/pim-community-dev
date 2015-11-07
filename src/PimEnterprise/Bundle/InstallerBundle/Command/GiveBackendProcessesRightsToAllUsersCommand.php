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

use Doctrine\Common\Persistence\ObjectManager;
use PimEnterprise\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Add permissions to all backend process job instances to the user group "All"
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class GiveBackendProcessesRightsToAllUsersCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:installer:grant-backend-processes-accesses')
            ->setDescription('Add the group "ALL" permissions to all job instances used for backend processes.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Add the group "ALL" permissions to all job instances used for backend processes.');
        $groupAll = $this->getUserGroupRepository()->getDefaultUserGroup();
        $backendProcessCodes = $this->getJobInstanceCodes();
        $jobInstances = $this->getJobInstanceRepository()->findBy(['code' => $backendProcessCodes]);
        $jobManager = $this->getJobProfileAccessManager();
        foreach ($jobInstances as $jobInstance) {
            $jobManager->grantAccess($jobInstance, $groupAll, Attributes::EXECUTE);
        }
        $objectManager = $this->getObjectManager();
        $objectManager->flush();

        $output->writeln('<info>done !</info>');
    }

    /**
     * @return array
     */
    protected function getJobInstanceCodes()
    {
        return [
            'update_product_value',
            'add_product_value',
            'add_product_value_with_permission',
            'update_product_value_with_permission',
            'publish_product',
            'unpublish_product',
            'edit_common_attributes',
            'edit_common_attributes_with_permission',
            'add_to_variant_group',
            'set_attribute_requirements',
            'approve_product_draft',
            'refuse_product_draft',
            'apply_assets_mass_upload',
            'csv_product_quick_export',
            'csv_published_product_quick_export'
        ];
    }

    /**
     * @return JobInstanceRepository
     */
    protected function getJobInstanceRepository()
    {
        return $this->getContainer()->get('pim_import_export.repository.job_instance');
    }

    /**
     * @return JobProfileAccessManager
     */
    protected function getJobProfileAccessManager()
    {
        return $this->getContainer()->get('pimee_security.manager.job_profile_access');
    }

    /**
     * @return \Pim\Bundle\UserBundle\Entity\Repository\GroupRepository
     */
    protected function getUserGroupRepository()
    {
        return $this->getContainer()->get('pim_user.repository.group');
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        $class = $this->getContainer()->getParameter('pimee_security.entity.job_profile_access.class');
        $registry = $this->getContainer()->get('akeneo_storage_utils.doctrine.smart_manager_registry');

        return $registry->getManagerForClass($class);
    }
}
