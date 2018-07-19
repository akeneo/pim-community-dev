<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Component\Service;

use Akeneo\Pim\Automation\SuggestData\Component\Repository\ConfigurationRepositoryInterface;

/**
 * Retrieves a suggest data configuration and returns it normalized.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetNormalizedConfiguration
{
    /** @var ConfigurationRepositoryInterface */
    private $repository;

    /**
     * @param ConfigurationRepositoryInterface $repository
     */
    public function __construct(ConfigurationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $code
     *
     * @return array
     */
    public function fromCode(string $code): array
    {
        $configuration = $this->repository->findOneByCode($code);

        if (null === $configuration) {
            return [];
        }

        return $configuration->normalize();
    }
}
