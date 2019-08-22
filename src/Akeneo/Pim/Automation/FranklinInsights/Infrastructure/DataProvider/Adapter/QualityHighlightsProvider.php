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

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\QualityHighlightsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\QualityHighlights\QualityHighlightsWebService;

class QualityHighlightsProvider extends AbstractProvider implements QualityHighlightsProviderInterface
{
    /** @var QualityHighlightsWebService */
    private $api;

    public function __construct(
        ConfigurationRepositoryInterface $configurationRepository,
        QualityHighlightsWebService $api
    ) {
        parent::__construct($configurationRepository);

        $this->api = $api;
    }

    public function applyAttributeStructure(array $attributes)
    {
        $this->api->setToken($this->getToken());

        $this->api->save($attributes);
    }
}
