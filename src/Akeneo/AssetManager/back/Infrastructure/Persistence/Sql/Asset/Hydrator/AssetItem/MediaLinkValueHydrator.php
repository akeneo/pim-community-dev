<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem\ImagePreviewUrlGenerator;

/**
 * AssetItem Value hydrator for value of type "Url".
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class MediaLinkValueHydrator implements ValueHydratorInterface
{
    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof MediaLinkAttribute;
    }

    public function hydrate($normalizedValue, AbstractAttribute $attribute, array $context = []): array
    {
//        TODO unify models
//        https://akeneo.atlassian.net/browse/AST-183
//        $url = sprintf('%s%s%s', $attribute->getPrefix()->normalize(), $normalizedValue['data'], $attribute->getSuffix()->normalize());
//        $normalizedValue['data'] = [
//            'filePath'         => $normalizedValue['data'],
//            'originalFilename' => basename($url)
//        ];

        return $normalizedValue;
    }
}
