<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\VersioningBundle\Reverter;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Model\Version;
use PimEnterprise\Bundle\VersioningBundle\Exception\RevertException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Product version reverter that allow to revert a product to a previous snapshot
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductReverter
{
    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var ManagerRegistry */
    protected $registry;

    /** @var SaverInterface */
    protected $productSaver;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ManagerRegistry       $registry
     * @param DenormalizerInterface $denormalizer
     * @param SaverInterface        $productSaver
     * @param ValidatorInterface    $validator
     */
    public function __construct(
        ManagerRegistry $registry,
        DenormalizerInterface $denormalizer,
        SaverInterface $productSaver,
        ValidatorInterface $validator
    ) {
        $this->registry     = $registry;
        $this->denormalizer = $denormalizer;
        $this->productSaver = $productSaver;
        $this->validator    = $validator;
    }

    /**
     * Revert an entity to a previous version
     *
     * @param Version $version
     *
     * @throws RevertException
     */
    public function revert(Version $version)
    {
        $class      = $version->getResourceName();
        $data       = $version->getSnapshot();
        $resourceId = $version->getResourceId();

        $currentObject = $this->registry->getRepository($class)->find($resourceId);

        if ($this->isImpactedByVariantGroup($currentObject)) {
            throw new RevertException(
                'Product can not be reverted because it belongs to a variant group'
            );
        }

        $revertedObject = $this->denormalizer->denormalize(
            $data,
            $class,
            'csv',
            [
                'entity'                  => $currentObject,
                'use_relative_media_path' => true
            ]
        );

        $violationsList = $this->validator->validate($revertedObject);
        if ($violationsList->count() > 0) {
            throw new RevertException('This version can not be restored. Some errors occurred during the validation.');
        }

        $this->productSaver->save($revertedObject);
    }

    /**
     * @param mixed $object
     *
     * @return boolean
     */
    protected function isImpactedByVariantGroup($object)
    {
        return $object instanceof ProductInterface && null !== $object->getVariantGroup();
    }
}
