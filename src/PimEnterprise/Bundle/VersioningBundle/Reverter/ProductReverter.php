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
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\Versioning\Model\Version;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PimEnterprise\Bundle\VersioningBundle\Exception\RevertException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Product version reverter that allow to revert a product to a previous snapshot
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

    /** @var TranslatorInterface */
    protected $translator;

    /** @var ArrayConverterInterface */
    protected $converter;

    /**
     * @param ManagerRegistry         $registry
     * @param ObjectUpdaterInterface  $productUpdater
     * @param SaverInterface          $productSaver
     * @param ValidatorInterface      $validator
     * @param TranslatorInterface     $translator
     * @param ArrayConverterInterface $converter
     */
    public function __construct(
        ManagerRegistry $registry,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        ArrayConverterInterface $converter
    ) {
        $this->registry = $registry;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
        $this->validator = $validator;
        $this->translator = $translator;
        $this->converter = $converter;
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
        $class = $version->getResourceName();
        $resourceId = $version->getResourceId();

        $currentObject = $this->registry->getRepository($class)->find($resourceId);

        $currentObject->getValues()->clear();

        $standardProduct = $this->converter->convert($version->getSnapshot());
        $this->productUpdater->update($currentObject, $standardProduct);

        $violationsList = $this->validator->validate($currentObject);
        if ($violationsList->count() > 0) {
            throw new RevertException(
                $this->translator->trans(
                    'flash.error.revert.product'
                )
            );
        }

        $this->productSaver->save($currentObject);
    }
}
