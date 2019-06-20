<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\Datagrid\Query;

use Akeneo\Pim\Enrichment\AssetManager\Component\Query\GetAssetInformationQueryInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Query\AssetInformation;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetDetailsInterface;

/**
 * This query acts as an anti corruption layer.
 *
 * It depends on a service defined in another bounded context to create its own results.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAssetInformationQuery implements GetAssetInformationQueryInterface
{
    /** @var FindAssetDetailsInterface */
    private $findAssetDetails;

    public function __construct(FindAssetDetailsInterface $findAssetDetails)
    {
        $this->findAssetDetails = $findAssetDetails;
    }

    public function fetch(string $assetFamilyIdentifier, string $assetCode): AssetInformation
    {
        $assetDetails = $this->findAssetDetails->find(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AssetCode::fromString($assetCode)
        );

        if (null === $assetDetails) {
            throw new \LogicException(
                sprintf(
                    'There was no information to fetch for asset family "%s" and asset code "%s"',
                    $assetFamilyIdentifier, $assetCode
                )
            );
        }

        $assetInformation = new AssetInformation();
        $assetInformation->assetFamilyIdentifier = $assetFamilyIdentifier;
        $assetInformation->code = $assetCode;
        $assetInformation->labels = $assetDetails->labels->normalize();

        return $assetInformation;
    }
}
