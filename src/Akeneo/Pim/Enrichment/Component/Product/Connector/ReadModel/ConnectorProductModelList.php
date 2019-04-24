<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConnectorProductModelList
{
    /** @var int total number of product models returned by the search without the pagination */
    private $totalNumberOfProductModels;

    /** @var ConnectorProductModel[] paginated list of product models for the connectors */
    private $connectorProductModels;

    public function __construct(int $totalNumberOfProductModels, array $connectorProductModels)
    {
        $this->totalNumberOfProductModels = $totalNumberOfProductModels;
        $this->connectorProductModels = (function (ConnectorProductModel ...$connectorProductModels) {
            return $connectorProductModels;
        })(...$connectorProductModels);
    }

    public function totalNumberOfProductModels(): int
    {
        return $this->totalNumberOfProductModels;
    }

    public function connectorProductModels(): array
    {
        return $this->connectorProductModels;
    }
}
