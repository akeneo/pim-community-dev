<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Bundle\CatalogBundle\Doctrine\EnterpriseCompletenessGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * Saver for an asset
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var SavingOptionsResolverInterface */
    protected $optionsResolver;

    /** @var EnterpriseCompletenessGeneratorInterface */
    protected $completenessGenerator;

    /**
     * @param ObjectManager                            $objectManager
     * @param SavingOptionsResolverInterface           $optionsResolver
     * @param EnterpriseCompletenessGeneratorInterface $completenessGenerator
     */
    public function __construct(
        ObjectManager $objectManager,
        SavingOptionsResolverInterface $optionsResolver,
        EnterpriseCompletenessGeneratorInterface $completenessGenerator
    ) {
        $this->objectManager         = $objectManager;
        $this->optionsResolver       = $optionsResolver;
        $this->completenessGenerator = $completenessGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function save($asset, array $options = [])
    {
        if (!$asset instanceof AssetInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Component\ProductAsset\Model\AssetInterface", "%s" provided.',
                    ClassUtils::getClass($asset)
                )
            );
        }

        $options = $this->optionsResolver->resolveSaveOptions($options);
        $this->objectManager->persist($asset);

        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        if (true === $options['schedule']) {
            $this->completenessGenerator->scheduleForAsset($asset);
        }
    }

    /**
     * @param AssetInterface[] $assets
     * @param array $options
     */
    public function saveAll(array $assets, array $options = [])
    {
        $options = [
            'flush'    => false,
            'schedule' => true,
        ];

        foreach ($assets as $asset) {
            $this->save($asset, $options);
        }

        $this->objectManager->flush();
    }
}
