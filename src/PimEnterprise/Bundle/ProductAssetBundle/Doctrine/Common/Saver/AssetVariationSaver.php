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

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Bundle\CatalogBundle\Doctrine\EnterpriseCompletenessGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;

/**
 * Saver for an asset variation
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetVariationSaver implements SaverInterface
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
    public function save($variation, array $options = [])
    {
        if (!$variation instanceof VariationInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Component\ProductAsset\Model\VariationInterface", "%s" provided.',
                    ClassUtils::getClass($variation)
                )
            );
        }

        $options = $this->optionsResolver->resolveSaveOptions($options);
        $this->objectManager->persist($variation);

        if (true === $options['schedule']) {
            $this->completenessGenerator->scheduleForAsset($variation->getAsset());
        }

        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }
}
