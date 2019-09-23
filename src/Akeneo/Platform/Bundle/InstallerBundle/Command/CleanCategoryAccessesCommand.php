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
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\RegistryInterface;
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

    /** @var CategoryAccessRepository */
    private $accessRepository;

    /** @var CategoryAccessRepository */
    private $categoryAccessRepository;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var ObjectManager */
    private $objectManager;

    public function __construct(
        CategoryAccessRepository $accessRepository,
        CategoryAccessRepository $categoryAccessRepository,
        GroupRepositoryInterface $groupRepository,
        ObjectManager $objectManager
    ) {
        parent::__construct();

        $this->accessRepository = $accessRepository;
        $this->categoryAccessRepository = $categoryAccessRepository;
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Removing the group "ALL" from categories\' permissions...');
        $groupAll = $this->groupRepository->getDefaultUserGroup();
        $this->accessRepository->revokeAccessToGroups([$groupAll]);
        $this->categoryAccessRepository->revokeAccessToGroups([$groupAll]);
        $this->objectManager->flush();
        $output->writeln('<info>done !</info>');
    }
}
