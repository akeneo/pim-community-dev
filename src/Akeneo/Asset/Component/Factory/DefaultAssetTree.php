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

use Akeneo\Asset\Component\Repository\AssetCategoryRepositoryInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\UserManagement\Component\Factory\DefaultProperty;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DefaultAssetTree implements DefaultProperty
{
    /** @var AssetCategoryRepositoryInterface */
    private $assetCategoryRepository;

    public function __construct(AssetCategoryRepositoryInterface $assetCategoryRepository)
    {
        $this->assetCategoryRepository = $assetCategoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function mutate(UserInterface $user): UserInterface
    {
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
