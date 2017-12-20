<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Security\Updater\Setter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Updater\Setter\AbstractFieldSetter;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Check if product associated is at least "viewable" to be associated to a product

 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class GrantedAssociationFieldSetter extends AbstractFieldSetter implements FieldSetterInterface
{
    /** @var FieldSetterInterface */
    private $associationFieldSetter;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /**
     * @param FieldSetterInterface                  $categoryFieldSetter
     * @param AuthorizationCheckerInterface         $authorizationChecker
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param array                                 $supportedFields
     */
    public function __construct(
        FieldSetterInterface $categoryFieldSetter,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $productRepository,
        array $supportedFields
    ) {
        $this->associationFieldSetter = $categoryFieldSetter;
        $this->authorizationChecker = $authorizationChecker;
        $this->productRepository = $productRepository;
        $this->supportedFields = $supportedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldData($product, $field, $data, array $options = [])
    {
        $this->associationFieldSetter->setFieldData($product, $field, $data, $options);

        foreach ($data as $associations) {
            if (!isset($associations['products'])) {
                continue;
            }

            foreach ($associations['products'] as $associatedProductIdentifier) {
                $associatedProduct = $this->productRepository->findOneByIdentifier($associatedProductIdentifier);
                if (!$this->authorizationChecker->isGranted([Attributes::VIEW], $associatedProduct)) {
                    throw new ResourceAccessDeniedException(
                        $associatedProduct,
                        'You cannot associate a product on which you have not a view permission.'
                    );
                }
            }
        }
    }
}
