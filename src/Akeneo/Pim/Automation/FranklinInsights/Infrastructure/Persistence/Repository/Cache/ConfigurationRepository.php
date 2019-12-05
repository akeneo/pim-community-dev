<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Cache;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;

class ConfigurationRepository implements ConfigurationRepositoryInterface
{
    /** @var ConfigurationRepositoryInterface */
    private $realRepository;

    /** @var ?Configuration */
    private $configuration;

    public function __construct(ConfigurationRepositoryInterface $realRepository)
    {
        $this->realRepository = $realRepository;
    }

    /**
     * @inheritdoc}
     */
    public function find(): Configuration
    {
        if (null === $this->configuration) {
            $this->configuration = $this->realRepository->find();
        }

        return $this->configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Configuration $configuration): void
    {
        $this->realRepository->save($configuration);
    }

    /**
     * @inheritdoc}
     */
    public function clear(): void
    {
        $this->realRepository->clear();
    }
}
