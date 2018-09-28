<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Connector\Processor\MassEdit\Asset;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor to change assets' categories
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ClassifyAssetsProcessor extends AbstractProcessor
{
    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * @param ObjectUpdaterInterface             $updater
     * @param ValidatorInterface                 $validator
     * @param AuthorizationCheckerInterface|null $authorizationChecker
     *
     * @todo merge : remove nullable on $authorizationChecker in master branch
     */
    public function __construct(
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authorizationChecker = null
    ) {
        $this->updater = $updater;
        $this->validator = $validator;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     *
     * @todo merge : remove null check on authorizationChecker in master branch
     */
    public function process($asset)
    {
        if (null !== $this->authorizationChecker && !$this->authorizationChecker->isGranted(Attributes::EDIT, $asset)) {
            $this->stepExecution->addWarning(
                'pimee_product_asset.not_editable',
                ['%code%' => $asset->getCode()],
                new DataInvalidItem($asset)
            );
            $this->stepExecution->incrementSummaryInfo('skipped_assets');

            return null;
        }

        $actions = $this->getConfiguredActions();

        $this->updateAsset($asset, $actions);

        if (!$this->isAssetValid($asset)) {
            $this->stepExecution->incrementSummaryInfo('skipped_assets');

            return null;
        }

        return $asset;
    }

    /**
     * Update given $asset from $actions
     *
     * @param AssetInterface $asset
     * @param array          $actions
     */
    protected function updateAsset(AssetInterface $asset, array $actions)
    {
        foreach ($actions as $action) {
            $this->updater->update($asset, [$action['field'] => $action['value']]);
        }
    }

    /**
     * Validate the asset
     *
     * @param AssetInterface $asset
     *
     * @return bool
     */
    protected function isAssetValid(AssetInterface $asset)
    {
        $violations = $this->validator->validate($asset);
        $this->addWarningMessage($violations, $asset);

        return 0 === $violations->count();
    }
}
