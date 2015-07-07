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
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;

/**
 * Saver for an asset reference
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetReferenceSaver implements SaverInterface
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
    public function save($reference, array $options = [])
    {
        if (!$reference instanceof ReferenceInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Component\ProductAsset\Model\ReferenceInterface", "%s" provided.',
                    ClassUtils::getClass($reference)
                )
            );
        }

        $options = $this->optionsResolver->resolveSaveOptions($options);
        $this->objectManager->persist($reference);

        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        if (true === $options['schedule']) {
            $this->completenessGenerator->scheduleForAsset($reference->getAsset());
        }
    }
}
