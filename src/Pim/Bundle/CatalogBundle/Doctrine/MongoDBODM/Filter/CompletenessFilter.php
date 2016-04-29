<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Expr;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
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
class CompletenessFilter extends AbstractFilter implements FieldFilterInterface
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var array */
    protected $supportedFields;

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
     */
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
    }

    /**
     * {@inheritdoc}
     *
     * If locale is omitted, all products having a matching completeness for
     * one of the locales of the specified scope will be selected.
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        $this->checkValue($field, $value, $locale, $scope);

        $localeCodes = (null !== $locale) ?
            [$locale] :
            $this->getChannelByCode($scope)->getLocaleCodes();

        foreach ($localeCodes as $localeCode) {
            $field = sprintf(
                "%s.%s.%s-%s",
                ProductQueryUtility::NORMALIZED_FIELD,
                'completenesses',
                $scope,
                $localeCode
            );
            $value = intval($value);

            $this->qb->addOr($this->getExpr($value, $field, $operator));
        }

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param string      $field
     * @param mixed       $value
     * @param string|null $locale
     * @param string|null $scope
     */
    protected function checkValue($field, $value, $locale, $scope)
    {
        if (!is_numeric($value)) {
            throw InvalidArgumentException::numericExpected($field, 'filter', 'completeness', gettype($value));
        }

        if (null === $scope) {
            throw InvalidArgumentException::scopeExpected($field, 'filter', 'completeness');
        }
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
}
