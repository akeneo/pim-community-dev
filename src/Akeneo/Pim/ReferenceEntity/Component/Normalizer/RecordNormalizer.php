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

namespace Akeneo\Pim\ReferenceEntity\Component\Normalizer;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($record, $format = null, array $context = [])
    {
        $code = $record->getCode()->__toString();
        if (key_exists('field_name', $context)) {
            return [$context['field_name'] => $code];
        }

        return $code;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Record && ('standard' === $format || 'storage' === $format || 'flat' === $format);
    }
}
