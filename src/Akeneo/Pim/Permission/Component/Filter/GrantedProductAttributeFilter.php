<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Filter;

use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUser;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

/**
 * Filter granted product values belonging to the parents.
 * A product value is granted if attribute and locale are visible.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GrantedProductAttributeFilter implements AttributeFilterInterface
{
    /** @var AttributeFilterInterface */
    private $productAttributeFilter;

    /** @var GetViewableAttributeCodesForUserInterface */
    private $getViewableAttributeCodesForUser;

    /** @var GetAllViewableLocalesForUser */
    private $getViewableLocalesForUser;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        AttributeFilterInterface $productAttributeFilter,
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser,
        GetAllViewableLocalesForUser $getViewableLocalesForUser,
        TokenStorageInterface $tokenStorage
    ) {
        $this->productAttributeFilter = $productAttributeFilter;
        $this->getViewableAttributeCodesForUser = $getViewableAttributeCodesForUser;
        $this->getViewableLocalesForUser = $getViewableLocalesForUser;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $standardProduct): array
    {
        $userId = $this->getUserId();

        if (array_key_exists('values', $standardProduct) && is_array($standardProduct['values']) && -1 !== $userId) {
            $viewableLocaleCodes = $this->getViewableLocalesForUser->fetchAll($userId);
            $attributeCodes = array_map('strval', array_keys($standardProduct['values']));
            $grantedAttributeCodes = array_flip(
                $this->getViewableAttributeCodesForUser->forAttributeCodes($attributeCodes, $userId)
            );

            foreach ($standardProduct['values'] as $attributeCode => $values) {
                if (!isset($grantedAttributeCodes[(string)$attributeCode])) {
                    throw UnknownPropertyException::unknownProperty($attributeCode);
                }

                if (is_array($values)) {
                    foreach ($values as $value) {
                        $this->checkGrantedLocale((string)$attributeCode, $value, $viewableLocaleCodes);
                    }
                }
            }
        }

        return $this->productAttributeFilter->filter($standardProduct);
    }

    /**
     * @throws UnknownPropertyException
     */
    private function checkGrantedLocale(string $attributeCode, array $value, array $grantedLocaleCodes): void
    {
        if (!isset($value['locale'])) {
            return;
        }

        if (!in_array($value['locale'], $grantedLocaleCodes)) {
            throw new UnknownPropertyException(
                $value['locale'],
                sprintf(
                    'Attribute "%s" expects an existing and activated locale, "%s" given.',
                    $attributeCode,
                    $value['locale']
                )
            );
        }
    }

    private function getUserId(): int
    {
        if (null === $this->tokenStorage->getToken() || null === $this->tokenStorage->getToken()->getUser()) {
            throw new \RuntimeException('Could not find any authenticated user');
        }

        $user = $this->tokenStorage->getToken()->getUser();
        Assert::implementsInterface($user, UserInterface::class);
        if (null === $user->getId()) {
            if (UserInterface::SYSTEM_USER_NAME === $user->getUsername()) {
                return -1;
            }
            throw new \RuntimeException('Could not find any authenticated user');
        }

        return $user->getId();
    }
}
