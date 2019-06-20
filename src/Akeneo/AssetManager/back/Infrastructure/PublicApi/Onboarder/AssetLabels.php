<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Onboarder;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class AssetLabels
{
    /** @var string */
    private $identifier;

    /** @var array  */
    private $labels;

    /** @var string */
    private $code;

    /** @var string */
    private $assetFamilyIdentifier;

    public function __construct(string $identifier, array $labels, string $code, string $assetFamilyIdentifier)
    {
        $this->identifier = $identifier;
        $this->labels = $labels;
        $this->code = $code;
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getCode(): string
    {
        return $this->code;
    }
    
    public function getAssetFamilyIdentifier(): string
    {
        return $this->assetFamilyIdentifier;
    }
}
