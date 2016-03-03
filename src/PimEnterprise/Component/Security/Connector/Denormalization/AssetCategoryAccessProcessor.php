<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Connector\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\MissingIdentifierException;
use Pim\Component\Connector\Processor\Denormalization\AbstractProcessor;
use PimEnterprise\Bundle\SecurityBundle\Entity\AssetCategoryAccess;
use PimEnterprise\Component\Security\Factory\AssetCategoryAccessFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Asset Category Access processor
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AssetCategoryAccessProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $accessConverter;

    /** @var AssetCategoryAccessFactory */
    protected $accessFactory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param StandardArrayConverterInterface $accessConverter
     * @param AssetCategoryAccessFactory $accessFactory
     * @param ObjectUpdaterInterface $updater
     * @param ValidatorInterface $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        StandardArrayConverterInterface $accessConverter,
        AssetCategoryAccessFactory $accessFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->accessConverter = $accessConverter;
        $this->accessFactory   = $accessFactory;
        $this->updater         = $updater;
        $this->validator       = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $categoryAccesses = [];
        $convertedItems = $this->accessConverter->convert($item);
        foreach ($convertedItems as $convertedItem) {
            $categoryAccess = $this->findOrCreateAssetCategoryAccess($convertedItem);
            $categoryAccesses[] = $categoryAccess;

            try {
                $this->updater->update($categoryAccess, $convertedItem);
            } catch (\InvalidArgumentException $exception) {
                $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
            }

            $violations = $this->validator->validate($categoryAccess);
            if ($violations->count() > 0) {
                $this->skipItemWithConstraintViolations($item, $violations);
            }
        }

        return $categoryAccesses;
    }

    /**
     * @param array $convertedItem
     *
     * @return AssetCategoryAccess
     *
     * @throws MissingIdentifierException
     */
    protected function findOrCreateAssetCategoryAccess(array $convertedItem)
    {
        $localeAccess = $this->findObject($this->repository, $convertedItem);
        if (null === $localeAccess) {
            return $this->accessFactory->create();
        }

        return $localeAccess;
    }
}
