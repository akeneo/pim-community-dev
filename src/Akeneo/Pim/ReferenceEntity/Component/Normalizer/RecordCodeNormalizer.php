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
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordCodeNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param RecordCode $recordCode
     */
    public function normalize($recordCode, $format = null, array $context = [])
    {
        return $recordCode->normalize();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof RecordCode && ('standard' === $format || 'storage' === $format || 'flat' === $format);
    }
}
