<?php

declare(strict_types=1);

namespace spec\AkeneoEnterprise\Connectivity\Connection\Infrastructure\Apps\Normalizer;

use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Permission\Bundle\Normalizer\Flat\AttributeGroupNormalizer;
use AkeneoEnterprise\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAllAppsUserGroupLabelQuery;
use PhpSpec\ObjectBehavior;

class AttributeGroupAccessNormalizerSpec extends ObjectBehavior
{
    public function let(
        AttributeGroupNormalizer $attributeGroupNormalizer,
        GetAllAppsUserGroupLabelQuery $getAllAppsUserGroupLabelQuery,
    ): void
    {
        $this->beConstructedWith(
            $attributeGroupNormalizer,
            $getAllAppsUserGroupLabelQuery,
        );
    }

    public function it_replaces_app_codes_by_app_labels(
        AttributeGroupNormalizer $attributeGroupNormalizer,
        GetAllAppsUserGroupLabelQuery $getAllAppsUserGroupLabelQuery,
    ): void
    {
        $attributeGroup = new AttributeGroup();
        $getAllAppsUserGroupLabelQuery->execute()->willReturn([['code' => 'app_123456789', 'label' => 'App Label']]);
        $attributeGroupNormalizer->normalize($attributeGroup, 'flat', [])->willReturn([
            'view_permission' => 'foo,bar,app_123456789',
            'edit_permission' => 'bar',
        ]);

        $this->normalize($attributeGroup, 'flat')->shouldReturn([
            'view_permission' => 'foo,bar,App Label',
            'edit_permission' => 'bar',
        ]);
    }

    public function it_returns_the_original_flat_attribute_group(
        AttributeGroupNormalizer $attributeGroupNormalizer,
        GetAllAppsUserGroupLabelQuery $getAllAppsUserGroupLabelQuery,
    ): void
    {
        $attributeGroup = new AttributeGroup();
        $getAllAppsUserGroupLabelQuery->execute()->willReturn([]);
        $attributeGroupNormalizer->normalize($attributeGroup, 'flat', [])->willReturn([
            'view_permission' => 'foo,bar,app_123456789',
            'edit_permission' => 'bar',
        ]);

        $this->normalize($attributeGroup, 'flat')->shouldReturn([
            'view_permission' => 'foo,bar,app_123456789',
            'edit_permission' => 'bar',
        ]);
    }
}
