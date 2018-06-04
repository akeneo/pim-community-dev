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

namespace PimEnterprise\Component\SuggestData\Query;

use PimEnterprise\Component\SuggestData\Repository\ConfigurationRepositoryInterface;

/**
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
    public function query(string $code): array
    {
        $configuration = $this->repository->find($code);

        if (null === $configuration) {
            return [];
        }

        return $configuration->normalize();
    }
}
