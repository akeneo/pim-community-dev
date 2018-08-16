<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;

/**
 * Family variant filter for elasticsearch query.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /** @var FamilyVariantRepositoryInterface */
    protected $familyVariantRepository;

    /**
     * @param FamilyVariantRepositoryInterface $familyVariantRepository
     * @param array                            $supportedFields
     * @param array                            $supportedOperators
     */
    public function __construct(
        FamilyVariantRepositoryInterface $familyVariantRepository,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
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
                        'family_variant' => $value,
                    ],
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_IN_LIST:
                $clause = [
                    'terms' => [
                        'family_variant' => $value,
                    ],
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;
            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => ['field' => 'family_variant'],
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;
            case Operators::IS_NOT_EMPTY:
                $clause = [
                    'exists' => ['field' => 'family_variant'],
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

            if (null === $this->familyVariantRepository->findOneByIdentifier($value)) {
                throw new ObjectNotFoundException(
                    sprintf('Object "family_variant" with code "%s" does not exist', $value)
                );
            }
        }
    }
}
