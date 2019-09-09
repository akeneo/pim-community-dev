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
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectFamiliesToApplyQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\QualityHighlights\QualityHighlightsWebService;

class QualityHighlightsProvider extends AbstractProvider implements QualityHighlightsProviderInterface
{
    /** @var QualityHighlightsWebService */
    private $api;

    /** @var SelectFamiliesToApplyQueryInterface */
    private $selectFamiliesToApplyQuery;

    public function __construct(
        ConfigurationRepositoryInterface $configurationRepository,
        QualityHighlightsWebService $api,
        SelectFamiliesToApplyQueryInterface $selectFamiliesToApplyQuery
    ) {
        parent::__construct($configurationRepository);

        $this->api = $api;
        $this->selectFamiliesToApplyQuery = $selectFamiliesToApplyQuery;
    }

    public function applyAttributeStructure(array $attributes): void
    {
        $this->api->setToken($this->getToken());

        $this->api->applyAttributes($attributes);
    }

    public function deleteAttribute(string $attributeCode): void
    {
        $this->api->setToken($this->getToken());

        $this->api->deleteAttribute($attributeCode);
    }

    public function applyFamilies(array $familyCodes): void
    {
        $families = $this->selectFamiliesToApplyQuery->execute($familyCodes);

        $this->api->setToken($this->getToken());

        $this->api->applyFamilies(['families' => $families]);
    }

    public function deleteFamily(string $familyCode): void
    {
        $this->api->setToken($this->getToken());

        $this->api->deleteFamily($familyCode);
    }

    public function applyProducts(array $products): void
    {
        $this->api->setToken($this->getToken());

        $this->api->applyProducts(['products' => $products]);
    }

    public function deleteProduct(int $productId): void
    {
        $this->api->setToken($this->getToken());

        $this->api->deleteProduct($productId);
    }
}
