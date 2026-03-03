<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ValidateProperties
{
    private static $productFields = [
        'uuid',
        'family',
        'categories',
        'completeness',
        'identifier',
        'created',
        'updated',
        'enabled',
        'groups',
        'parent',
        'quality_score',
    ];

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @throws InvalidQueryException
     */
    public function validate(array $search): void
    {
        foreach ($search as $propertyCode => $filters) {
            $propertyCode = (string) $propertyCode;
            $isExistingAttribute = $this->isExistingAttribute($propertyCode);

            if (!$this->isProductField($propertyCode) && !$isExistingAttribute) {
                throw new InvalidQueryException(
                    sprintf(
                        '"%s" does not exist or you do not have permission to access it.',
                        $propertyCode
                    ),
                    404
                );
            }
        }
    }

    private function isProductField(string $propertyCode): bool
    {
        return in_array($propertyCode, self::$productFields);
    }

    private function isExistingAttribute(string $propertyCode): bool
    {
        return null !== $this->attributeRepository->findOneByIdentifier($propertyCode);
    }
}
