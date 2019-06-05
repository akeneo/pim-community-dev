<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Api\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateGrantedAttributesInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
final class ValidateGrantedAttributes implements ValidateGrantedAttributesInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

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
    public function validate(?array $attributeCodes): void
    {
        if (null === $attributeCodes) {
            return;
        }

        $errors = [];
        foreach ($attributeCodes as $attributeCode) {
            $attributeCode = trim($attributeCode);
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            $group = $attribute->getGroup();

            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $group)) {
                $errors[] = $attributeCode;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Attributes "%s" do not exist.' : 'Attribute "%s" does not exist.';
            throw new InvalidQueryException(sprintf($plural, implode(', ', $errors)));
        }
    }
}
