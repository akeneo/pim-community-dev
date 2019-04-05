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

namespace Specification\Akeneo\Pim\Permission\Component\Normalizer\Authorization;

use Akeneo\Pim\Permission\Component\Normalizer\Authorization\ProductDraftNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductDraftNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductDraftNormalizer::class);
    }

    function it_only_supports_product_draft_in_authorization_format(EntityWithValuesDraftInterface $productDraft)
    {
        $this->supportsNormalization($productDraft, 'internal_api')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'authorization')->shouldReturn(false);
        $this->supportsNormalization($productDraft, 'authorization')->shouldReturn(true);
    }

    function it_normalizes_a_product_draft(EntityWithValuesDraftInterface $productDraft)
    {
        $productDraft->getId()->willReturn(42);
        $productDraft->getAuthor()->willReturn('sandra');

        $this->normalize($productDraft, 'authorization')->shouldReturn([
            'id' => 42,
            'author' => 'sandra',
        ]);
    }
}
