<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product draft normalizer
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProductDraftNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($productDraft, $format = null, array $context = [])
    {
        return [
            'id'      => $productDraft->getId(),
            'author'  => $productDraft->getAuthor(),
            'created' => $productDraft->getCreatedAt(),
            'changes' => $productDraft->getChanges(),
            'status'  => $productDraft->getStatus()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof EntityWithValuesDraftInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
