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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\IdentifiersMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\IdentifiersMapping\IdentifiersMappingWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class IdentifiersMappingProvider extends AbstractProvider implements IdentifiersMappingProviderInterface
{
    /** @var IdentifiersMappingWebService */
    private $api;

    /** @var IdentifiersMappingNormalizer */
    private $normalizer;

    /**
     * @param IdentifiersMappingWebService $api
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(
        IdentifiersMappingWebService $api,
        ConfigurationRepositoryInterface $configurationRepository,
        IdentifiersMappingNormalizer $normalizer
    ) {
        parent::__construct($configurationRepository);

        $this->api = $api;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function saveIdentifiersMapping(IdentifiersMapping $identifiersMapping): void
    {
        $this->api->setToken($this->getToken());

        try {
            $this->api->save($this->normalizer->normalize($identifiersMapping));
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw DataProviderException::authenticationError($e);
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        }
    }
}
