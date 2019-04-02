<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

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
            foreach ($filters as $filter) {
                if (
                    !in_array($propertyCode, self::$productFields) &&
                    null === $this->attributeRepository->findOneByIdentifier($propertyCode)
                ) {
                    throw new InvalidQueryException(
                        sprintf(
                            'Filter on property "%s" is not supported or does not support operator "%s"',
                            $propertyCode,
                            $filter['operator']
                        )
                    );
                }
            }
        }
    }
}
