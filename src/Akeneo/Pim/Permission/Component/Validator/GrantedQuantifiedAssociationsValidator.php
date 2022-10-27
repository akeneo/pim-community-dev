<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\QuantifiedAssociations;
use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\Pim\Permission\Component\Query\ProductModelCategoryAccessQueryInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class GrantedQuantifiedAssociationsValidator extends ConstraintValidator
{
    public function __construct(
        private ProductCategoryAccessQueryInterface $productCategoryAccessQuery,
        private ProductModelCategoryAccessQueryInterface $productModelCategoryAccessQuery,
        private TokenStorageInterface $tokenStorage,
    ) {
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
        $productIdentifiers = array_filter(array_column($productQuantifiedLinks, 'identifier'));
        $productUuids = array_map(
            fn (string $uuid): UuidInterface => Uuid::fromString($uuid),
            array_filter(array_column($productQuantifiedLinks, 'uuid'))
        );

        $grantedProductIdentifiers = $this->productCategoryAccessQuery->getGrantedProductIdentifiers(
            $productIdentifiers,
            $user
        );
        $grantedProductUuids = $this->productCategoryAccessQuery->getGrantedProductUuids(
            $productUuids,
            $user
        );

        $nonGrantedProductIdentifiers = array_diff($productIdentifiers, $grantedProductIdentifiers);
        $nonGrantedProductUuids = array_diff($productUuids, $grantedProductUuids);
        $nonGrantedProducts = array_merge($nonGrantedProductIdentifiers, $nonGrantedProductUuids);

        if (count($nonGrantedProducts) > 0) {
            $this->context->buildViolation(
                GrantedQuantifiedAssociations::PRODUCTS_DO_NOT_EXIST_MESSAGE,
                [
                    '{{ values }}' => implode(', ', $nonGrantedProducts),
                ]
            )
                ->atPath($propertyPath)
                ->setCode(QuantifiedAssociations::PRODUCTS_DO_NOT_EXIST_ERROR)
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
                ->setCode(QuantifiedAssociations::PRODUCT_MODELS_DO_NOT_EXIST_ERROR)
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
