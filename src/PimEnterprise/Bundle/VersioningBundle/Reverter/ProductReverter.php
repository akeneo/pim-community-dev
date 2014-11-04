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

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\VersioningBundle\Model\Version;
use PimEnterprise\Bundle\VersioningBundle\Exception\RevertException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Product version reverter that allow to revert a product to a previous snapshot
 *
 * @author    Romain Monceau <romain@akeneo.com>
 */
class ProductReverter
{
    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var ManagerRegistry */
    protected $registry;

    /** @var ProductManager */
    protected $productManager;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ManagerRegistry     $registry
     * @param SerializerInterface $serializer
     * @param ProductManager      $productManager
     * @param ValidatorInterface  $validator
     */
    public function __construct(
        ManagerRegistry $registry,
        DenormalizerInterface $denormalizer,
        ProductManager $productManager,
        ValidatorInterface $validator
    ) {
        $this->registry       = $registry;
        $this->denormalizer   = $denormalizer;
        $this->productManager = $productManager;
        $this->validator      = $validator;
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
        $revertedObject = $this->denormalizer->denormalize($data, $class, "csv", ['entity' => $currentObject]);

        /** @var \Symfony\Component\Validator\ConstraintViolationList $violationsList */
        $violationsList = $this->validator->validate($revertedObject);
        if ($violationsList->count() > 0) {
            throw new RevertException('This version can not be restored. Some errors occured during the validation.');
        }

        $this->productManager->saveProduct($revertedObject);
    }
}
