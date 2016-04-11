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
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Processor\Denormalization\AbstractProcessor;
use PimEnterprise\Component\Security\Model\LocaleAccessInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Locale Access processor
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class LocaleAccessProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $accessConverter;

    /** @var SimpleFactoryInterface */
    protected $accessFactory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param StandardArrayConverterInterface       $accessConverter
     * @param SimpleFactoryInterface                $accessFactory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        StandardArrayConverterInterface $accessConverter,
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
        $localeAccesses = [];
        $convertedItems = $this->accessConverter->convert($item);
        foreach ($convertedItems as $convertedItem) {
            $localeAccess = $this->findOrCreateLocaleAccess($convertedItem);
            $localeAccesses[] = $localeAccess;

            try {
                $this->updater->update($localeAccess, $convertedItem);
            } catch (\InvalidArgumentException $exception) {
                $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
            }

            $violations = $this->validator->validate($localeAccess);
            if (0 < $violations->count()) {
                $this->skipItemWithConstraintViolations($item, $violations);
            }
        }

        return $localeAccesses;
    }

    /**
     * @param array $convertedItem
     *
     * @return LocaleAccessInterface
     */
    protected function findOrCreateLocaleAccess(array $convertedItem)
    {
        $localeAccess = $this->repository->findOneByIdentifier(
            sprintf('%s.%s', $convertedItem['locale'], $convertedItem['user_group'])
        );
        if (null === $localeAccess) {
            $localeAccess = $this->accessFactory->create();
        }

        return $localeAccess;
    }
}
