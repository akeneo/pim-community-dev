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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\Transformer;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindCodesByIdentifiersInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetConnectorValueTransformer implements ConnectorValueTransformerInterface
{
    /** @var FindCodesByIdentifiersInterface */
    private $findCodesByIdentifiers;

    public function __construct(FindCodesByIdentifiersInterface $findCodesByIdentifiers)
    {
        $this->findCodesByIdentifiers = $findCodesByIdentifiers;
    }

    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof AssetAttribute;
    }

    public function transform(array $normalizedValue, AbstractAttribute $attribute): ?array
    {
        Assert::true($this->supports($attribute));

        $assetIdentifier = AssetIdentifier::fromString($normalizedValue['data']);
        $assetCodes = $this->findCodesByIdentifiers->find([$assetIdentifier]);

        if (empty($assetCodes)) {
            return null;
        }

        return [
            'locale'  => $normalizedValue['locale'],
            'channel' => $normalizedValue['channel'],
            'data'    => (string) current($assetCodes),
        ];
    }
}
