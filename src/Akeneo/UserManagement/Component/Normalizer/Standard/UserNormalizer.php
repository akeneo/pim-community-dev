<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Normalizer\Standard;

use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(private readonly DateTimeNormalizer $dateTimeNormalizer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($user, $format = null, array $context = []): array
    {
        /** @var UserInterface $user */
        Assert::isInstanceOf($user, UserInterface::class);

        $normalizedAvatar = $user->getAvatar() ? [
            'filePath' => $user->getAvatar()->getKey(),
            'originalFilename' => $user->getAvatar()->getOriginalFilename(),
        ] : null;

        return [
            'username' => $user->getUserIdentifier(),
            'enabled' => $user->isEnabled(),
            'name_prefix' => $user->getNamePrefix(),
            'first_name' => $user->getFirstName(),
            'middle_name' => $user->getMiddleName(),
            'last_name' => $user->getLastName(),
            'name_suffix' => $user->getNameSuffix(),
            'phone' => $user->getPhone(),
            'email' => $user->getEmail(),
            'avatar' => $normalizedAvatar,
            'catalog_default_locale' => $user->getCatalogLocale()->getCode(),
            'catalog_default_scope' => $user->getCatalogScope()->getCode(),
            'default_category_tree' => $user->getDefaultTree()->getCode(),
            'user_default_locale' => $user->getUiLocale()->getCode(),
            'timezone' => $user->getTimezone(),
            'groups' => array_filter($user->getGroupNames(), function (string $groupName) {
                return $groupName !== User::GROUP_DEFAULT;
            }),
            'roles' => $user->getRolesCollection()->map(
                fn (RoleInterface $role): string => $role->getRole()
            )->getValues(),
            'product_grid_filters' => $user->getProductGridFilters(),
            'default_product_grid_view' => $user->getDefaultGridView('product-grid') ?
                $user->getDefaultGridView('product-grid')->getLabel() :
                null,
            'date_account_created' => $this->dateTimeNormalizer->normalize($user->getCreatedAt(), $format, $context),
            'date_account_last_updated' => $this->dateTimeNormalizer->normalize($user->getUpdatedAt(), $format, $context),
            'last_logged_in' => $this->dateTimeNormalizer->normalize($user->getLastLogin(), $format, $context),
            'login_count' => $user->getLoginCount(),
        ];
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof UserInterface && \in_array($format, ['standard', 'array']);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
