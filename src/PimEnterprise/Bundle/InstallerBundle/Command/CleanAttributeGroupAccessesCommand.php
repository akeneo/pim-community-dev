<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\InstallerBundle\Command;

use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Revoke ALL group on attribute group accesses.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class CleanAttributeGroupAccessesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pimee:installer:clean-attribute-group-accesses')
            ->setDescription('Removing the group "ALL" from attribute groups\' permissions after a clean installation.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Removing the group "ALL" from attribute groups\' permissions...');
        $groupAll = $this->getUserGroupRepository()->getDefaultUserGroup();
        $this->getProductCategoryAccessRepository()->revokeAccessToGroups([$groupAll]);
        $output->writeln('<info>done !</info>');
    }

    /**
     * @return AttributeGroupAccessRepository
     */
    protected function getProductCategoryAccessRepository()
    {
        return $this->getContainer()->get('pimee_security.repository.attribute_group_access');
    }

    /**
     * @return \Pim\Bundle\UserBundle\Entity\Repository\GroupRepository
     */
    protected function getUserGroupRepository()
    {
        return $this->getContainer()->get('pim_user.repository.group');
    }
}
