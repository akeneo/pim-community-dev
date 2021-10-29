<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\InstallerBundle\Command;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Revoke ALL group on category accesses.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class CleanCategoryAccessesCommand extends Command
{
    protected static $defaultName = 'pimee:installer:clean-category-accesses';

    private CategoryAccessRepository $accessRepository;
    private GroupRepositoryInterface $groupRepository;
    private ObjectManager $objectManager;

    public function __construct(
        CategoryAccessRepository $accessRepository,
        GroupRepositoryInterface $groupRepository,
        ObjectManager $objectManager
    ) {
        parent::__construct();

        $this->accessRepository = $accessRepository;
        $this->groupRepository = $groupRepository;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Removing the group "ALL" from categories\' permissions after a clean installation.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Removing the group "ALL" from categories\' permissions...');
        $groupAll = $this->groupRepository->getDefaultUserGroup();
        $this->accessRepository->revokeAccessToGroups([$groupAll]);
        $this->objectManager->flush();
        $output->writeln('<info>done !</info>');

        return Command::SUCCESS;
    }
}
