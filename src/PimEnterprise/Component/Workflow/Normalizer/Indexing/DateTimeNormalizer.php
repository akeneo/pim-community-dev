<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Normalizer\Indexing;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a DateTime as ISO-8601 (with the timezone) to the indexing format.
 * See https://en.wikipedia.org/wiki/ISO_8601
 *
 * Example: 2017-06-13T12:07:58+00:00
 *
 * This format is based on the standard format.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class DateTimeNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $dateTimeNormalizer;

    /**
     * @param NormalizerInterface $dateTimeNormalizer
     */
    public function __construct(NormalizerInterface $dateTimeNormalizer)
    {
        $this->dateTimeNormalizer = $dateTimeNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($date, $format = null, array $context = [])
    {
        return $this->dateTimeNormalizer->normalize($date, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \DateTime && $format === ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX;
    }
}
