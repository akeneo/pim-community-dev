<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Factory;

use Akeneo\Asset\Component\Model\CategoryInterface;
use Akeneo\Asset\Component\Repository\AssetCategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * Creates and configures a user instance.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserFactory implements SimpleFactoryInterface
{
    /** @var SimpleFactoryInterface */
    private $userFactory;

    /** @var AssetCategoryRepositoryInterface */
    private $assetCategoryRepository;

    /**
     * SimpleFactoryInterface $userFactory
     * AssetCategoryRepositoryInterface $assetCategoryRepository
     */
    public function __construct(
        SimpleFactoryInterface $userFactory,
        AssetCategoryRepositoryInterface $assetCategoryRepository)
    {
        $this->userFactory = $userFactory;
        $this->assetCategoryRepository = $assetCategoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        /** @var UserInterface $user */
        $user = $this->userFactory->create();

        if (null !== $defaultAssetTree = $this->getDefaultAssetTree()) {
            $user->addProperty('default_asset_tree', $defaultAssetTree->getCode());
        }

        return $user;
    }

    /**
     * @return CategoryInterface|null when we install the pim
     */
    private function getDefaultAssetTree(): ?CategoryInterface
    {
        $roots = $this->assetCategoryRepository->findRoot();

        if (count($roots) === 0) {
            return null;
        }

        return array_values($roots)[0];
    }
}
