<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\User\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;

/**
 * Updates an user
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserUpdater implements ObjectUpdaterInterface
{
    /** @var ObjectUpdaterInterface */
    private $userUpdater;

    /** @var IdentifiableObjectRepositoryInterface */
    private $categoryAssetRepository;

    public function __construct(
        ObjectUpdaterInterface $userUpdater,
        IdentifiableObjectRepositoryInterface $categoryAssetRepository
    ) {
        $this->categoryAssetRepository = $categoryAssetRepository;
        $this->userUpdater = $userUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function update($user, array $data, array $options = [])
    {
        $ceData = array_filter($data, function ($field) {
            return !in_array($field, ['default_asset_tree', 'proposals_to_review_notification', 'proposals_state_notifications']);
        }, ARRAY_FILTER_USE_KEY);

        $this->userUpdater->update($user, $ceData, $options);

        foreach ($data as $field => $value) {
            switch ($field) {
                case 'default_asset_tree':
                    $user->setDefaultAssetTree($this->findAssetCategory($value));
                    break;
                case 'proposals_to_review_notification':
                    $user->setProposalsToReviewNotification($value);
                    break;
                case 'proposals_state_notifications':
                    $user->setProposalsStateNotification($value);
                    break;
            }
        }

        return $this;
    }

    /**
     * Get tree entity from category code
     *
     * @param string $code
     *
     * @throws InvalidPropertyException
     *
     * @return CategoryInterface
     */
    protected function findAssetCategory($code)
    {
        $category = $this->categoryAssetRepository->findOneByIdentifier($code);

        if (null === $category) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'default_asset_tree',
                'category code',
                'The category does not exist',
                static::class,
                $code
            );
        }

        return $category;
    }
}
