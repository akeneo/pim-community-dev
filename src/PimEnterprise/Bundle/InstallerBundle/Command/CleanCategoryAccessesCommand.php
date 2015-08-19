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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Revoke ALL group on category accesses.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class CleanCategoryAccessesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pimee:installer:clean-category-accesses')
            ->setDescription('Removing the group "ALL" from categories\' permissions after a clean installation.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Removing the group "ALL" from categories\' permissions...');
        $groupAll = $this->getUserGroupRepository()->getDefaultUserGroup();
        $this->getCategoryAccessRepository()->revokeAccessToGroups([$groupAll]);
        $output->writeln('<info>done !</info>');
    }

    /**
     * @return \PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository
     */
    protected function getCategoryAccessRepository()
    {
        return $this->getContainer()->get('pimee_security.repository.category_access');
    }

    /**
     * @return \Pim\Bundle\UserBundle\Entity\Repository\GroupRepository
     */
    protected function getUserGroupRepository()
    {
        return $this->getContainer()->get('pim_user.repository.group');
    }
}
