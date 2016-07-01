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

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
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

    /**
     * @param ObjectUpdaterInterface $updater
     * @param ValidatorInterface     $validator
     */
    public function __construct(ObjectUpdaterInterface $updater, ValidatorInterface $validator)
    {
        $this->updater = $updater;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($asset)
    {
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
