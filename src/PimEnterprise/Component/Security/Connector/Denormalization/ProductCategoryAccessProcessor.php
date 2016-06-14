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

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Connector\Processor\Denormalization\AbstractProcessor;
use PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Product Category Access processor
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ProductCategoryAccessProcessor extends AbstractProcessor
{
    /** @var SimpleFactoryInterface */
    protected $accessFactory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param SimpleFactoryInterface                $accessFactory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactoryInterface $accessFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->accessFactory   = $accessFactory;
        $this->updater         = $updater;
        $this->validator       = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $categoryAccess = $this->findOrCreateProductCategoryAccess($item);

        try {
            $this->updater->update($categoryAccess, $item);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($categoryAccess);
        if (0 < $violations->count()) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $categoryAccess;
    }

    /**
     * @param array $convertedItem
     *
     * @return ProductCategoryAccess
     */
    protected function findOrCreateProductCategoryAccess(array $convertedItem)
    {
        $categoryAccess = $this->repository->findOneByIdentifier(
            sprintf('%s.%s', $convertedItem['category'], $convertedItem['user_group'])
        );
        if (null === $categoryAccess) {
            $categoryAccess = $this->accessFactory->create();
        }

        return $categoryAccess;
    }
}
