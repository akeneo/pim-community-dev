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

namespace PimEnterprise\Component\Catalog\Security\Merger;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
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
 * (@see \PimEnterprise\Component\Catalog\Security\Filter\NotGrantedAssociatedProductFilter)
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
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var FieldSetterInterface */
    private $associationSetter;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param FieldSetterInterface          $associationSetter
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        FieldSetterInterface $associationSetter
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->associationSetter = $associationSetter;
    }

    /**
     * {@inheritdoc}
     */
    public function merge($filteredProduct, $fullProduct = null)
    {
        if (!$filteredProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($filteredProduct), ProductInterface::class);
        }

        if (null === $fullProduct) {
            return $filteredProduct;
        }

        if (!$fullProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($fullProduct), ProductInterface::class);
        }

        $associationCodes = [];
        $hasAssociations = false;

        foreach ($fullProduct->getAssociations() as $association) {
            $associationCodes[$association->getAssociationType()->getCode()]['products'] = [];
            $associationCodes[$association->getAssociationType()->getCode()]['product_models'] = [];
            $hasAssociations = true;

            foreach ($association->getProducts() as $associatedProduct) {
                if (!$this->authorizationChecker->isGranted([Attributes::VIEW], $associatedProduct)) {
                    $associationCodes[$association->getAssociationType()->getCode()]['products'][] = $associatedProduct->getIdentifier();
                }
            }
            foreach ($association->getProductModels() as $associatedProductModel) {
                if (!$this->authorizationChecker->isGranted([Attributes::VIEW], $associatedProductModel)) {
                    $associationCodes[$association->getAssociationType()->getCode()]['product_models'][] = $associatedProductModel->getCode();
                }
            }
        }

        foreach ($filteredProduct->getAssociations() as $association) {
            $hasAssociations = true;
            foreach ($association->getProducts() as $associatedProduct) {
                $associationCodes[$association->getAssociationType()->getCode()]['products'][] = $associatedProduct->getIdentifier();
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
