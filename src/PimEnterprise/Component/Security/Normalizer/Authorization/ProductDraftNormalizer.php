<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Normalizer\Authorization;

use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductDraftNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($productDraft, $format = null, array $context = [])
    {
        return [
            'id'     => $productDraft->getId(),
            'author' => $productDraft->getAuthor(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EntityWithValuesDraftInterface && 'authorization' === $format;
    }
}
