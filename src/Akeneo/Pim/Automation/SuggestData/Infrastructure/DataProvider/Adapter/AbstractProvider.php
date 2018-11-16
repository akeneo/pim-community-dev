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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
abstract class AbstractProvider
{
    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var Token */
    private $token;

    /**
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(ConfigurationRepositoryInterface $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * @return string
     */
    protected function getToken(): string
    {
        if (null === $this->token) {
            $config = $this->configurationRepository->find();
            if ($config instanceof Configuration) {
                $this->token = $config->getToken();
            }
        }

        return (string) $this->token;
    }
}
