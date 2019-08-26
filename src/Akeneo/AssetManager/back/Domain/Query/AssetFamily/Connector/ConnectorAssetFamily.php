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

namespace Akeneo\AssetManager\Domain\Query\AssetFamily\Connector;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorAssetFamily
{
    /** @var AssetFamilyIdentifier */
    private $identifier;

    /** @var LabelCollection */
    private $labelCollection;

    /** @var Image */
    private $image;

    /** @var array */
    private $productLinkRules;

    public function __construct(
        AssetFamilyIdentifier $identifier,
        LabelCollection $labelCollection,
        Image $image,
        array $productLinkRules
    ) {
        $this->identifier = $identifier;
        $this->labelCollection = $labelCollection;
        $this->image = $image;
        $this->productLinkRules = $productLinkRules;
    }

    public function normalize(): array
    {
        $normalizedLabels = $this->labelCollection->normalize();

        return [
            'code' => $this->identifier->normalize(),
            'labels' => empty($normalizedLabels) ? (object) [] : $normalizedLabels,
            'image' => $this->image->isEmpty() ? null : $this->image->getKey(),
            'product_link_rules' => $this->productLinkRules,
        ];
    }

    public function getIdentifier(): AssetFamilyIdentifier
    {
        return $this->identifier;
    }
}
