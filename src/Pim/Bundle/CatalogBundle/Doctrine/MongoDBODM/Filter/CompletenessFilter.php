<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Expr;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

/**
 * Completeness filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var array Allow to map complex operators to simpler operators */
    protected $operatorsMapping = [
        Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES => Operators::GREATER_OR_EQUAL_THAN,
        Operators::GREATER_THAN_ON_ALL_LOCALES           => Operators::GREATER_THAN,
        Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES   => Operators::LOWER_OR_EQUAL_THAN,
        Operators::LOWER_THAN_ON_ALL_LOCALES             => Operators::LOWER_THAN,
    ];

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param array                      $supportedFields
     * @param array                      $supportedOperators
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->channelRepository  = $channelRepository;
        $this->supportedFields    = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     *
     * If locale is omitted, all products having a matching completeness for
     * one of the locales of the specified scope will be selected.
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        $this->checkScopeAndValue($field, $scope, $value);

        $expr = $this->qb->expr();

        if (array_key_exists($operator, $this->operatorsMapping)) {
            $this->checkOptions($field, $options);

            foreach ($options['locales'] as $localeCode) {
                $field = $this->getNormalizedField($scope, $localeCode);
                $value = (int) $value;
                $expr->addAnd($this->getExpr($value, $field, $this->operatorsMapping[$operator]));
            }
        } else {
            $localeCodes = (null !== $locale) ?
                [$locale] :
                $this->getChannelByCode($scope)->getLocaleCodes();

            foreach ($localeCodes as $localeCode) {
                $field = $this->getNormalizedField($scope, $localeCode);
                $value = (int) $value;
                $expr->addOr($this->getExpr($value, $field, $operator));
            }
        }

        $this->qb->addAnd($expr);

        return $this;
    }

    /**
     * @param string $scope
     * @param string $localeCode
     *
     * @return string
     */
    protected function getNormalizedField($scope, $localeCode)
    {
        return sprintf(
            "%s.%s.%s-%s",
            ProductQueryUtility::NORMALIZED_FIELD,
            'completenesses',
            $scope,
            $localeCode
        );
    }

    /**
     * Get the expression corresponding to the given operator
     *
     * @param int    $value
     * @param string $field
     * @param string $operator
     *
     * @return Expr
     */
    protected function getExpr($value, $field, $operator)
    {
        $expr = $this->qb->expr();

        switch ($operator) {
            case Operators::EQUALS:
                $expr->field($field)->equals($value);
                break;
            case Operators::NOT_EQUAL:
                $expr->addAnd($this->qb->expr()->field($field)->exists(true));
                $expr->addAnd($this->qb->expr()->field($field)->notEqual($value));
                break;
            case Operators::LOWER_THAN:
                $expr->field($field)->lt($value);
                break;
            case Operators::GREATER_THAN:
                $expr->field($field)->gt($value);
                break;
            case Operators::LOWER_OR_EQUAL_THAN:
                $expr->field($field)->lte($value);
                break;
            case Operators::GREATER_OR_EQUAL_THAN:
                $expr->field($field)->gte($value);
                break;
        }

        return $expr;
    }

    /**
     * @param string $code
     *
     * @throws ObjectNotFoundException
     *
     * @return ChannelInterface
     */
    protected function getChannelByCode($code)
    {
        $channel = $this->channelRepository->findOneByIdentifier($code);
        if (null === $channel) {
            throw new ObjectNotFoundException(sprintf('Channel with "%s" code does not exist', $code));
        }

        return $channel;
    }

    /**
     * Check if scope and value are valid
     *
     * @throws InvalidArgumentException
     *
     * @param string $field
     * @param mixed  $scope
     * @param mixed  $value
     */
    protected function checkScopeAndValue($field, $scope, $value)
    {
        if (!is_numeric($value)) {
            throw InvalidArgumentException::numericExpected($field, 'filter', 'completeness', gettype($value));
        }

        if (null === $scope) {
            throw InvalidArgumentException::scopeExpected($field, 'filter', 'completeness');
        }
    }

    /**
     * Check if options are valid for complex operators
     *      GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES
     *      GREATER_THAN_ON_ALL_LOCALES
     *      LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES
     *      LOWER_THAN_ON_ALL_LOCALES
     *
     * @throws InvalidArgumentException
     *
     * @param string $field
     * @param array  $options
     */
    protected function checkOptions($field, array $options)
    {
        if (!array_key_exists('locales', $options)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $field,
                'locales',
                'filter',
                'completeness',
                print_r(array_keys($options), true)
            );
        }

        if (!isset($options['locales']) || !is_array($options['locales'])) {
            throw InvalidArgumentException::arrayOfArraysExpected(
                $field,
                'filter',
                'completeness',
                print_r($options, true)
            );
        }
    }
}
