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
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Processor\Denormalization\AbstractProcessor;
use PimEnterprise\Component\Security\Model\AttributeGroupAccessInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Attribute Group Access processor
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeGroupAccessProcessor extends AbstractProcessor
{
    /** @var ArrayConverterInterface */
    protected $accessConverter;

    /** @var SimpleFactoryInterface */
    protected $accessFactory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param ArrayConverterInterface               $accessConverter
     * @param SimpleFactoryInterface                $accessFactory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        ArrayConverterInterface $accessConverter,
        SimpleFactoryInterface $accessFactory,
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
        $groupAccesses = [];
        $convertedItems = $this->accessConverter->convert($item);
        foreach ($convertedItems as $convertedItem) {
            $groupAccess = $this->findOrCreateAttributeGroupAccess($convertedItem);
            $groupAccesses[] = $groupAccess;

            try {
                $this->updater->update($groupAccess, $convertedItem);
            } catch (\InvalidArgumentException $exception) {
                $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
            }

            $violations = $this->validator->validate($groupAccess);
            if (0 < $violations->count()) {
                $this->skipItemWithConstraintViolations($item, $violations);
            }
        }

        return $groupAccesses;
    }

    /**
     * @param array $convertedItem
     *
     * @return AttributeGroupAccessInterface
     */
    protected function findOrCreateAttributeGroupAccess(array $convertedItem)
    {
        $groupAccess = $this->repository->findOneByIdentifier(
            sprintf('%s.%s', $convertedItem['attribute_group'], $convertedItem['user_group'])
        );
        if (null === $groupAccess) {
            $groupAccess = $this->accessFactory->create();
        }

        return $groupAccess;
    }
}
