<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Validator\Constraint;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * Validate that the locale is activated.
 */
class AreGrantedAttributesValidator extends ConstraintValidator
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param AuthorizationCheckerInterface         $authorizationChecker
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($attributeCodes, Constraint $constraint)
    {
        Assert::isArray($attributeCodes, 'Attribute codes should be an array of string to validate that it is granted attributes.');
        $notGrantedAttributeCodes = [];

        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (null === $attributeCode || !$this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute)) {
                $notGrantedAttributeCodes[] = $attributeCode;
            }
        }

        if (!empty($notGrantedAttributeCodes)) {
            $this->context->buildViolation(
                $constraint->message,
                ['%attributes%' => implode(',', $notGrantedAttributeCodes)]
            )->addViolation();
        }
    }
}
