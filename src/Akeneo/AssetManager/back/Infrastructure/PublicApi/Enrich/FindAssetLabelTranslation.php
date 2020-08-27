<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Enrich;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\SqlFindAssetLabelsByCodes;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAssetLabelTranslation
{
    /** @var SqlFindAssetLabelsByCodes */
    private $sqlFindAssetLabelsByCodes;

    public function __construct(SqlFindAssetLabelsByCodes $sqlFindAssetLabelsByCodes)
    {
        $this->sqlFindAssetLabelsByCodes = $sqlFindAssetLabelsByCodes;
    }

    public function byFamilyCodeAndAssetCodes(string $familyCode, array $assetCodes, $locale): array
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($familyCode);
        $labelCollections = $this->sqlFindAssetLabelsByCodes->find($assetFamilyIdentifier, $assetCodes);

        $result = [];

        /** @var LabelCollection $labelCollection */
        foreach ($labelCollections as $assetCode => $labelCollection) {
            $result[$assetCode] = $labelCollection->getLabel($locale);
        }

        return $result;
    }
}
