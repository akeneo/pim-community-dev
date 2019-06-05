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

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateGrantedPropertiesInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
final class ValidateGrantedProperties implements ValidateGrantedPropertiesInterface
{
    private const PRODUCT_FIELDS = [
        'family',
        'categories',
        'completeness',
        'identifier',
        'created',
        'updated',
        'enabled',
        'groups',
    ];

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
    public function validate(array $search): void
    {
        foreach ($search as $propertyCode => $filters) {
            $property = trim($propertyCode);
            if (in_array($property, self::PRODUCT_FIELDS)) {
                continue;
            }

            foreach ($filters as $filter) {
                $attribute = $this->attributeRepository->findOneByIdentifier($property);

                if (null !== $attribute) {
                    if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute)) {
                        throw new InvalidQueryException(
                            sprintf(
                                'Filter on property "%s" is not supported or does not support operator "%s"',
                                $property,
                                $filter['operator']
                            )
                        );
                    }
                }
            }
        }
    }
}
