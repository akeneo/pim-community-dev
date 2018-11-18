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
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\IdentifiersMapping\IdentifiersMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class IdentifiersMappingProvider extends AbstractProvider implements IdentifiersMappingProviderInterface
{
    /** @var IdentifiersMappingApiInterface */
    private $api;

    /**
     * @param IdentifiersMappingApiInterface $api
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(
        IdentifiersMappingApiInterface $api,
        ConfigurationRepositoryInterface $configurationRepository
    ) {
        parent::__construct($configurationRepository);

        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    public function updateIdentifiersMapping(IdentifiersMapping $identifiersMapping): void
    {
        $this->api->setToken($this->getToken());
        $normalizer = new IdentifiersMappingNormalizer();

        $this->api->update($normalizer->normalize($identifiersMapping));
    }
}
