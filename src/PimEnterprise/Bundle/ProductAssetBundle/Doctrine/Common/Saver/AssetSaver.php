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
use PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
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

    /** @var CompletenessGeneratorInterface */
    protected $compGenerator;

    /**
     * @param ObjectManager                  $objectManager
     * @param SavingOptionsResolverInterface $optionsResolver
     * @param CompletenessGeneratorInterface $compGenerator
     */
    public function __construct(
        ObjectManager $objectManager,
        SavingOptionsResolverInterface $optionsResolver,
        CompletenessGeneratorInterface $compGenerator
    ) {
        $this->objectManager   = $objectManager;
        $this->optionsResolver = $optionsResolver;
        $this->compGenerator   = $compGenerator;
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
            $this->compGenerator->scheduleForAsset($asset);
        }
    }

    /**
     * @param AssetInterface[] $assets
     * @param array            $options
     */
    public function saveAll(array $assets, array $options = [])
    {
        $options = [
            'flush'    => false,
            'schedule' => false,
        ];

        foreach ($assets as $asset) {
            $this->save($asset, $options);
        }

        $this->objectManager->flush();
    }
}
