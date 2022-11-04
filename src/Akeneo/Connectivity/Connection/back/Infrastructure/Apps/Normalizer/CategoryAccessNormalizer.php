<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Connectivity\Connection\Infrastructure\Apps\Normalizer;

use Akeneo\Pim\Permission\Bundle\Normalizer\Flat\CategoryNormalizer;
use AkeneoEnterprise\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAllAppsUserGroupLabelQuery;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryAccessNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(
        private CategoryNormalizer $categoryNormalizer,
        private GetAllAppsUserGroupLabelQuery $getAllAppsUserGroupLabelQuery,
    ) {
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $this->categoryNormalizer->supportsNormalization($data, $format);
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $normalizedCategory = $this->categoryNormalizer->normalize($object, $format, $context);

        $userGroupLabels = $this->getAllAppsUserGroupLabelQuery->execute();

        if (empty($userGroupLabels)) {
            return $normalizedCategory;
        }

        $codeLabelMapping = \array_combine(
            \array_column($userGroupLabels, 'code'),
            \array_column($userGroupLabels, 'label'),
        );

        $normalizedCategory['view_permission'] = $this->replaceAppCodeByLabel($normalizedCategory['view_permission'], $codeLabelMapping);
        $normalizedCategory['edit_permission'] = $this->replaceAppCodeByLabel($normalizedCategory['edit_permission'], $codeLabelMapping);
        $normalizedCategory['own_permission'] = $this->replaceAppCodeByLabel($normalizedCategory['own_permission'], $codeLabelMapping);

        return $normalizedCategory;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->categoryNormalizer->hasCacheableSupportsMethod();
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
