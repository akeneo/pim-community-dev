<?php
declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Filter;

use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser;
use Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
        $viewableLocaleCodes = $this->getViewableLocalesForUser->fetchAll($userId);

        if (array_key_exists('values', $standardProduct) && is_array($standardProduct['values'])) {
            $attributeCodes = array_keys($standardProduct['values']);
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
     * @param string $attributeCode
     *
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
        if (null === $this->tokenStorage->getToken()) {
            throw new \RuntimeException('Could not find any authenticated user');
        }
        $user = $this->tokenStorage->getToken()->getUser();
        if (null === $user || null === $user->getId()) {
            throw new \RuntimeException('Could not find any authenticated user');
        }

        return $user->getId();
    }
}
