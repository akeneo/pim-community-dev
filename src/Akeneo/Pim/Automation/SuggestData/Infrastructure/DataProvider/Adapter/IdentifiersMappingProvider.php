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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\IdentifiersMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\IdentifiersMapping\IdentifiersMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class IdentifiersMappingProvider implements IdentifiersMappingProviderInterface
{
    /** @var IdentifiersMappingApiInterface */
    private $api;

    /** @var IdentifiersMappingNormalizer */
    private $normalizer;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var Token */
    private $token;

    /**
     * @param IdentifiersMappingApiInterface $api
     * @param IdentifiersMappingNormalizer $normalizer
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(
        IdentifiersMappingApiInterface $api,
        IdentifiersMappingNormalizer $normalizer,
        ConfigurationRepositoryInterface $configurationRepository
    ) {
        $this->api = $api;
        $this->normalizer = $normalizer;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function updateIdentifiersMapping(IdentifiersMapping $identifiersMapping): void
    {
        $this->api->setToken($this->getToken());
        $this->api->update($this->normalizer->normalize($identifiersMapping));
    }

    /**
     * @return string
     */
    private function getToken(): string
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
