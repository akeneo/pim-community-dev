<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\ProductRevert\Reverter;

use Akeneo\Pim\WorkOrganization\ProductRevert\Exception\ConstraintViolationsException;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Product version reverter that allows to revert a product to a previous snapshot.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductReverter
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var  ObjectUpdaterInterface*/
    protected $productUpdater;

    /** @var SaverInterface */
    protected $productSaver;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ArrayConverterInterface */
    protected $converter;

    /**
     * @param ManagerRegistry         $registry
     * @param ObjectUpdaterInterface  $productUpdater
     * @param SaverInterface          $productSaver
     * @param ValidatorInterface      $validator
     * @param ArrayConverterInterface $converter
     */
    public function __construct(
        ManagerRegistry $registry,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        ValidatorInterface $validator,
        ArrayConverterInterface $converter
    ) {
        $this->registry = $registry;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
        $this->validator = $validator;
        $this->converter = $converter;
    }

    /**
     * Revert an entity to a previous version
     *
     * @param Version $version
     *
     * @throws ConstraintViolationsException
     */
    public function revert(Version $version): void
    {
        $class = $version->getResourceName();
        $resourceId = $version->getResourceId();

        $currentObject = $this->registry->getRepository($class)->find($resourceId);

        $values = $currentObject->getValues();
        $values->clear();
        $currentObject->setValues($values);

        $standardProduct = $this->getStandardProductFromVersion($version);
        $this->productUpdater->update($currentObject, $standardProduct);

        $violationsList = $this->validator->validate($currentObject);
        if ($violationsList->count() > 0) {
            throw new ConstraintViolationsException($violationsList);
        }

        $this->productSaver->save($currentObject);
    }

    /**
     * Returns the standard product from a version snapshot.
     *
     * If a regular product was changed into a variant product (setting a parent
     * to it), then reverting this change would be the same than converting this
     * new variant product into a regular product, which is not allowed for now.
     *
     * To prevent that, we remove the parent field from the standard product data
     * if this field is null or empty.
     *
     * @param VersionInterface $version
     *
     * @return array
     */
    protected function getStandardProductFromVersion(VersionInterface $version): array
    {
        $standardProduct =$this->converter->convert($version->getSnapshot());

        if (!isset($standardProduct['parent']) || '' === $standardProduct['parent']) {
            unset($standardProduct['parent']);
        }

        return $standardProduct;
    }
}
