<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\Batch\Model\JobExecution;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Datetime filter for Updated field. It includes specific operators SINCE_LAST_EXPORT and SINCE_LAST_N_DAYS
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdatedDateTimeFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields    = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (Operators::SINCE_LAST_EXPORT === $operator) {
            $this->addUpdatedSinceLastExport($field, $options);
        } elseif (Operators::SINCE_LAST_N_DAYS === $operator) {
            $this->addSinceLastNDays($field, $value);
        }

        return $this;
    }

    /**
     * Add a filter for products updated since N ($value) days to the query builder
     *
     * @param string $field
     * @param string $value
     */
    protected function addSinceLastNDays($field, $value)
    {
        $fromDate = new \DateTime(sprintf('%s days ago', $value), new \DateTimeZone('UTC'));
        $updatedField = current($this->qb->getRootAliases()) . '.' . $field;

        $this->qb->andWhere(
            $this->qb->expr()->gt(
                $updatedField,
                $this->qb->expr()->literal($fromDate->format(static::DATETIME_FORMAT))
            )
        );
    }

    /**
     * Add a filter for products updated since the last export to the query builder
     *
     * @param string $field
     * @param array  $options ['lastJobExecution' => JobExecution]
     */
    protected function addUpdatedSinceLastExport($field, $options = [])
    {
        $this->checkOptions($field, $options);

        if (null !== $options['lastJobExecution']) {
            $lastJobEndTime = $options['lastJobExecution']->getEndTime();
            $updatedField   = current($this->qb->getRootAliases()) . '.' . $field;

            // TODO : "Greater than" or "Greater or equal than" ??
            $this->qb->andWhere(
                $this->qb->expr()->gt(
                    $updatedField,
                    $this->qb->expr()->literal($lastJobEndTime->format(static::DATETIME_FORMAT))
                )
            );
        }
    }

    protected function checkOptions($field, array $options)
    {
        if (!array_key_exists('lastJobExecution', $options)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $field,
                'lastJobExecution',
                'filter',
                'updated',
                print_r($options, true)
            );
        }

        if (!$options['lastJobExecution'] instanceof JobExecution && null !== $options['lastJobExecution']) {
            throw InvalidArgumentException::expected(
                $field,
                sprintf(
                    'option "lastJobExecution" to be an instance of "%s" or null value',
                    JobExecution::class
                ),
                'filter',
                'updated datetime',
                print_r($options['lastJobExecution'], true)
            );
        }
    }
}
