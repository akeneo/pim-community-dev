<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Connectivity\Connection\Infrastructure\Apps\Normalizer;

use Akeneo\Pim\Permission\Bundle\Normalizer\Flat\AttributeGroupNormalizer;
use AkeneoEnterprise\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAllAppsUserGroupLabelQuery;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeGroupAccessNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(
        private AttributeGroupNormalizer $attributeGroupNormalizer,
        private GetAllAppsUserGroupLabelQuery $getAllAppsUserGroupLabelQuery,
    ) {
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $this->attributeGroupNormalizer->supportsNormalization($data, $format);
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $normalizedAttributeGroup = $this->attributeGroupNormalizer->normalize($object, $format, $context);

        $userGroupLabels = $this->getAllAppsUserGroupLabelQuery->execute();

        if (empty($userGroupLabels)) {
            return $normalizedAttributeGroup;
        }

        $codeLabelMapping = \array_combine(
            \array_column($userGroupLabels, 'code'),
            \array_column($userGroupLabels, 'label'),
        );

        $normalizedAttributeGroup['view_permission'] = $this->replaceAppCodeByLabel($normalizedAttributeGroup['view_permission'], $codeLabelMapping);
        $normalizedAttributeGroup['edit_permission'] = $this->replaceAppCodeByLabel($normalizedAttributeGroup['edit_permission'], $codeLabelMapping);

        return $normalizedAttributeGroup;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->attributeGroupNormalizer->hasCacheableSupportsMethod();
    }

    /**
     * @param array<array-key<string>, string> $mapping
     */
    private function replaceAppCodeByLabel(string $permissions, array $mapping): string
    {
        $permissionsArray = \explode(',', $permissions);

        foreach ($permissionsArray as $key => $permission) {
            if (isset($mapping[$permission])) {
                $permissionsArray[$key] = $mapping[$permission];
            }
        }

        return \implode(',', $permissionsArray);
    }
}
