<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Akeneo\Pim\Permission\Bundle\MassEdit\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AddAttributeValueProcessor as BaseProcessor;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * It edits an entity with values but check if the user has right to mass edit the product (if he is the owner).
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class AddAttributeValueProcessor extends BaseProcessor
{
    protected $authorizationChecker;

    /**
     * @param ValidatorInterface                    $productValidator
     * @param ValidatorInterface                    $productModelValidator
     * @param PropertyAdderInterface                $propertyAdder
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param CheckAttributeEditable                $checkAttributeEditable
     * @param array                                 $supportedTypes
     * @param AuthorizationCheckerInterface         $authorizationChecker
     */
    public function __construct(
        ValidatorInterface $productValidator,
        ValidatorInterface $productModelValidator,
        PropertyAdderInterface $propertyAdder,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        CheckAttributeEditable $checkAttributeEditable,
        array $supportedTypes,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct(
            $productValidator,
            $productModelValidator,
            $propertyAdder,
            $attributeRepository,
            $checkAttributeEditable,
            $supportedTypes
        );

        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function isEntityEditable(EntityWithFamilyInterface $entity): bool
    {
        if (!$this->authorizationChecker->isGranted(Attributes::OWN, $entity)
            && !$this->authorizationChecker->isGranted(Attributes::EDIT, $entity)
        ) {
            return false;
        }

        return parent::isEntityEditable($entity);
    }
}
