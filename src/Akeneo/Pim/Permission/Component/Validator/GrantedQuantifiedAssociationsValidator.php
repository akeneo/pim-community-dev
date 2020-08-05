<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\Pim\Permission\Component\Query\ProductModelCategoryAccessQueryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class GrantedQuantifiedAssociationsValidator extends ConstraintValidator
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ProductCategoryAccessQueryInterface
     */
    private $productCategoryAccessQuery;

    /**
     * @var ProductModelCategoryAccessQueryInterface
     */
    private $productModelCategoryAccessQuery;

    public function __construct(
        ProductCategoryAccessQueryInterface $productCategoryAccessQuery,
        ProductModelCategoryAccessQueryInterface $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage
    ) {
        $this->productCategoryAccessQuery = $productCategoryAccessQuery;
        $this->productModelCategoryAccessQuery = $productModelCategoryAccessQuery;
        $this->tokenStorage = $tokenStorage;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof QuantifiedAssociationCollection) {
            throw new UnexpectedTypeException($value, QuantifiedAssociationCollection::class);
        }

        if (!$constraint instanceof GrantedQuantifiedAssociations) {
            throw new UnexpectedTypeException($constraint, GrantedQuantifiedAssociations::class);
        }

        $this->disablePropertyPathNormalization($constraint);

        $quantifiedAssociationNormalized = $value->normalize();

        $user = $this->tokenStorage->getToken()->getUser();
        foreach ($quantifiedAssociationNormalized as $associationTypeCode => $targets) {
            $propertyPath = sprintf('%s', $associationTypeCode);
            $productsPropertyPath = sprintf('%s.products', $propertyPath);
            $productModelsPropertyPath = sprintf('%s.product_models', $propertyPath);

            $this->validateAccessGrantedOnProductQuantifiedLinks(
                $targets['products'],
                $user,
                $productsPropertyPath
            );

            $this->validateAccessGrantedOnProductModelQuantifiedLinks(
                $targets['product_models'],
                $user,
                $productModelsPropertyPath
            );
        }
    }

    private function validateAccessGrantedOnProductQuantifiedLinks(
        array $productQuantifiedLinks,
        UserInterface $user,
        string $propertyPath
    ) {
        $productIdentifiers = array_column($productQuantifiedLinks, 'identifier');
        $grantedProductIdentifiers = $this->productCategoryAccessQuery->getGrantedProductIdentifiers(
            $productIdentifiers,
            $user
        );

        $nonGrantedProductIdentifiers = array_diff($productIdentifiers, $grantedProductIdentifiers);

        if (count($nonGrantedProductIdentifiers) > 0) {
            $this->context->buildViolation(
                GrantedQuantifiedAssociations::PRODUCTS_DO_NOT_EXIST_MESSAGE,
                [
                    '{{ values }}' => implode(', ', $nonGrantedProductIdentifiers),
                ]
            )
                ->atPath($propertyPath)
                ->addViolation();
        }
    }

    private function validateAccessGrantedOnProductModelQuantifiedLinks(
        array $productModelQuantifiedLinks,
        UserInterface $user,
        string $propertyPath
    ) {
        $productModelCodes = array_column($productModelQuantifiedLinks, 'identifier');
        $grantedProductModelCodes = $this->productModelCategoryAccessQuery->getGrantedProductModelCodes(
            $productModelCodes,
            $user
        );

        $nonGrantedProductModelCodes = array_diff($productModelCodes, $grantedProductModelCodes);

        if (count($nonGrantedProductModelCodes) > 0) {
            $this->context->buildViolation(
                GrantedQuantifiedAssociations::PRODUCT_MODELS_DO_NOT_EXIST_MESSAGE,
                [
                    '{{ values }}' => implode(', ', $nonGrantedProductModelCodes),
                ]
            )
                ->atPath($propertyPath)
                ->addViolation();
        }
    }

    /**
     * The Violation normalizer rename property paths with uppercase characters, this method disable this behavior
     * @see https://github.com/akeneo/pim-community-dev/blob/9b9f18385d51f5d2147a79374bd5b5aa9eae3464/src/Akeneo/Tool/Component/Api/Normalizer/Exception/ViolationNormalizer.php#L144
     */
    private function disablePropertyPathNormalization(GrantedQuantifiedAssociations $constraint): void
    {
        $constraint->payload['normalize_property_path'] = false;
    }
}
