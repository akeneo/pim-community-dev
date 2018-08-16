<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

/**
 * Family filter for an Elasticsearch query
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /**
     * @param FamilyRepositoryInterface $familyRepository
     * @param array                     $supportedFields
     * @param array                     $supportedOperators
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->familyRepository = $familyRepository;
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter(
        $field,
        $operator,
        $value,
        $locale = null,
        $channel = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($field, $value);
        }

        switch ($operator) {
            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        'family.code' => $value,
                    ],
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_IN_LIST:
                $clause = [
                    'terms' => [
                        'family.code' => $value,
                    ],
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;
            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => ['field' => 'family.code'],
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;
            case Operators::IS_NOT_EMPTY:
                $clause = [
                    'exists' => ['field' => 'family.code'],
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     *
     * @throws ObjectNotFoundException
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, static::class);

            if (null === $this->familyRepository->findOneByIdentifier($value)) {
                throw new ObjectNotFoundException(
                    sprintf('Object "family" with code "%s" does not exist', $value)
                );
            }
        }
    }
}
