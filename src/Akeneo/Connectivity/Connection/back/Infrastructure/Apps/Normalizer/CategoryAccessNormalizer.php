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

    public function supportsNormalization($data, string $format = null)
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

        $viewPermissions = explode(',', $normalizedCategory['view_permission']);
        $editPermissions = explode(',', $normalizedCategory['edit_permission']);
        $ownPermissions = explode(',', $normalizedCategory['own_permission']);

        foreach ($userGroupLabels as $userGroupLabel) {
            $code = $userGroupLabel['code'];
            $label = $userGroupLabel['label'];

            $viewPermissions = $this->replaceAppCodeByLabel($viewPermissions, $code, $label);
            $editPermissions = $this->replaceAppCodeByLabel($editPermissions, $code, $label);
            $ownPermissions = $this->replaceAppCodeByLabel($ownPermissions, $code, $label);
        }

        $normalizedCategory['view_permission'] = implode(',', $viewPermissions);
        $normalizedCategory['edit_permission'] = implode(',', $editPermissions);
        $normalizedCategory['own_permission'] = implode(',', $ownPermissions);

        return $normalizedCategory;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->categoryNormalizer->hasCacheableSupportsMethod();
    }

    /**
     * @return array<string>
     */
    private function replaceAppCodeByLabel(array $permissions, string $code, string $label): array
    {
        foreach ($permissions as $key => $permission) {
            if ($code === $permission) {
                $permissions[$key] = $label;
            }
        }

        return $permissions;
    }
}
