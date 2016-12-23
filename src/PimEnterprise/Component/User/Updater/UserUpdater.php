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

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\User\Updater\UserUpdater as BaseUserUpdater;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;

/**
 * Updates an user
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserUpdater extends BaseUserUpdater
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $categoryAssetRepository;

    public function __construct(
        UserManager $userManager,
        IdentifiableObjectRepositoryInterface $categoryRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $roleRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        IdentifiableObjectRepositoryInterface $categoryAssetRepository
    ) {
        parent::__construct(
            $userManager,
            $categoryRepository,
            $localeRepository,
            $channelRepository,
            $roleRepository,
            $groupRepository
        );

        $this->categoryAssetRepository = $categoryAssetRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function setData(UserInterface $user, $field, $data)
    {
        switch ($field) {
            case 'defaultAssetTree':
                $user->setDefaultAssetTree($this->findAssetCategory($data));
                break;
            case 'proposals_to_review_notification':
                $user->setProposalsToReviewNotification($data);
                break;
            case 'proposals_state_notifications':
                $user->setProposalsStateNotification($data);
                break;
        }

        parent::setData($user, $field, $data);
    }

    /**
     * Get tree entity from category code
     *
     * @param string $code
     *
     * @return CategoryInterface
     */
    protected function findAssetCategory($code)
    {
        $category = $this->categoryAssetRepository->findOneByIdentifier($code);

        if (null === $category) {
            throw new \InvalidArgumentException(sprintf('Category %s was not found', $code));
        }

        return $category;
    }
}
