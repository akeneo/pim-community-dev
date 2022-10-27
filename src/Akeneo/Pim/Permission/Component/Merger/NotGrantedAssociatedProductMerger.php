<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Merger;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Merge not granted associations with new associations. Example:
 * In database, your product "my_product" contains those associations:
 * {
 *    "associations": {
 *      "products": ["product_a", "product_b", "product_c"]
 *    }
 * }
 *
 * But "product_a" is not viewable by the connected user.
 * That's means when he will get the product "my_product", the application will return:
 * {
 *    "associations": {
 *      "products": ["product_b", "product_c"]
 *    }
 * }
 * (@see \Akeneo\Pim\Permission\Component\Filter\NotGrantedAssociatedProductFilter)
 *
 * When user will update "my_product":
 * {
 *    "associations": {
 *      "products": ["product_c"]
 *    }
 * }
 * we have to merge not granted data (here "product_a") before saving data in database.
 *
 * Finally, "my_product" will contain:
 * {
 *    "associations": {
 *      "products": ["product_a", "product_c"]
 *    }
 * }
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class NotGrantedAssociatedProductMerger implements NotGrantedDataMergerInterface
{
    // TODO Remove this
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var FieldSetterInterface */
    private $associationSetter;

    /** @var ItemCategoryAccessQuery */
    private $productCategoryAccessQuery;

    /** @var ItemCategoryAccessQuery */
    private $productModelCategoryAccessQuery;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param FieldSetterInterface          $associationSetter
     * @param ItemCategoryAccessQuery       $productCategoryAccessQuery
     * @param ItemCategoryAccessQuery       $productModelCategoryAccessQuery
     * @param TokenStorageInterface         $tokenStorage
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        FieldSetterInterface $associationSetter,
        ItemCategoryAccessQuery $productCategoryAccessQuery,
        ItemCategoryAccessQuery $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->associationSetter = $associationSetter;
        $this->productCategoryAccessQuery = $productCategoryAccessQuery;
        $this->productModelCategoryAccessQuery = $productModelCategoryAccessQuery;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function merge($filteredProduct, $fullProduct = null)
    {
        if (!$filteredProduct instanceof EntityWithAssociationsInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($filteredProduct), EntityWithAssociationsInterface::class);
        }

        if (null === $fullProduct) {
            return $filteredProduct;
        }

        if (!$fullProduct instanceof EntityWithAssociationsInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($fullProduct), EntityWithAssociationsInterface::class);
        }

        $associationCodes = [];
        $hasAssociations = false;

        $user = $this->tokenStorage->getToken()->getUser();

        foreach ($fullProduct->getAssociations() as $association) {
            $associationCodes[$association->getAssociationType()->getCode()] = [
                'product_uuids' => [],
                'product_models' => [],
                'groups' => [],
            ];
            $hasAssociations = true;

            $associatedProducts = $association->getProducts();
            $grantedProductUuids = \array_flip($this->productCategoryAccessQuery->getGrantedProductUuids($associatedProducts->toArray(), $user));

            foreach ($associatedProducts as $associatedProduct) {
                if (!isset($grantedProductUuids[$associatedProduct->getUuid()->toString()])) {
                    $associationCodes[$association->getAssociationType()->getCode()]['product_uuids'][] = $associatedProduct->getUuid()->toString();
                }
            }

            $associatedProductModels = $association->getProductModels();
            $grantedProductModelIds = $this->productModelCategoryAccessQuery->getGrantedItemIds($associatedProductModels->toArray(), $user);

            foreach ($associatedProductModels as $associatedProductModel) {
                if (!isset($grantedProductModelIds[$associatedProductModel->getId()])) {
                    $associationCodes[$association->getAssociationType()->getCode()]['product_models'][] = $associatedProductModel->getCode();
                }
            }
        }

        foreach ($filteredProduct->getAssociations() as $association) {
            $hasAssociations = true;
            if (!isset($associationCodes[$association->getAssociationType()->getCode()])) {
                $associationCodes[$association->getAssociationType()->getCode()] = [
                    'product_uuids' => [],
                    'product_models' => [],
                    'groups' => [],
                ];
            }

            foreach ($association->getProducts() as $associatedProduct) {
                $associationCodes[$association->getAssociationType()->getCode()]['product_uuids'][] = $associatedProduct->getUuid()->toString();
            }
            foreach ($association->getProductModels() as $associatedProductModel) {
                $associationCodes[$association->getAssociationType()->getCode()]['product_models'][] = $associatedProductModel->getCode();
            }
            foreach ($association->getGroups() as $associatedGroup) {
                $associationCodes[$association->getAssociationType()->getCode()]['groups'][] = $associatedGroup->getCode();
            }
        }

        if ($hasAssociations || !empty($associationCodes)) {
            $this->associationSetter->setFieldData($fullProduct, 'associations', $associationCodes);
        }

        return $fullProduct;
    }
}
