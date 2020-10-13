<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetNormalizer
{
    public static function fromSerializer(SerializerInterface $serializer): NormalizerInterface
    {
        Assert::implementsInterface($serializer, NormalizerInterface::class);

        return $serializer;
    }
}
