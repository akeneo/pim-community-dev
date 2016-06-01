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
use PimEnterprise\Component\Security\Model\JobProfileAccessInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Job Profile Access processor
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class JobProfileAccessProcessor extends AbstractProcessor
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
        $jobAccesses = [];
        $convertedItems = $this->accessConverter->convert($item);
        foreach ($convertedItems as $convertedItem) {
            $jobAccess = $this->findOrCreateJobProfileAccess($convertedItem);
            $jobAccesses[] = $jobAccess;

            try {
                $this->updater->update($jobAccess, $convertedItem);
            } catch (\InvalidArgumentException $exception) {
                $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
            }

            $violations = $this->validator->validate($jobAccess);
            if (0 < $violations->count()) {
                $this->skipItemWithConstraintViolations($item, $violations);
            }
        }

        return $jobAccesses;
    }

    /**
     * @param array $convertedItem
     *
     * @return JobProfileAccessInterface
     */
    protected function findOrCreateJobProfileAccess(array $convertedItem)
    {
        $jobAccess = $this->repository->findOneByIdentifier(
            sprintf('%s.%s', $convertedItem['job_profile'], $convertedItem['user_group'])
        );
        if (null === $jobAccess) {
            $jobAccess = $this->accessFactory->create();
        }

        return $jobAccess;
    }
}
