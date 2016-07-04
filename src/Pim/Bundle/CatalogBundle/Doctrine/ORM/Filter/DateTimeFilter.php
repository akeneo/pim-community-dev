<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Datetime filter
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /** @var JobRepositoryInterface */
    protected $jobRepository;

    /** @var JobInstanceRepository */
    protected $jobInstanceRepository;

    /**
     * @param JobInstanceRepository  $jobInstanceRepository
     * @param JobRepositoryInterface $jobRepository
     * @param array                  $supportedFields
     * @param array                  $supportedOperators
     */
    public function __construct(
        JobInstanceRepository $jobInstanceRepository,
        JobRepositoryInterface $jobRepository,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields       = $supportedFields;
        $this->supportedOperators    = $supportedOperators;
        $this->jobRepository         = $jobRepository;
        $this->jobInstanceRepository = $jobInstanceRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        switch ($operator) {
            case Operators::SINCE_LAST_EXPORT:
                if (!is_string($value)) {
                    throw InvalidArgumentException::stringExpected($field, 'filter', 'updated', gettype($value));
                }

                $this->addUpdatedSinceLastExport($field, $value);
                break;

            case Operators::SINCE_LAST_N_DAYS:
                if (!is_numeric($value)) {
                    throw InvalidArgumentException::numericExpected($field, 'filter', 'updated', gettype($value));
                }

                $this->addSinceLastNDays($field, $value);
                break;

            case Operators::NOT_BETWEEN:
                $values = $this->formatValues($field, $value);
                $field = current($this->qb->getRootAliases()) . '.' . $field;
                $this->applyNotBetweenFilter($field, $values);
                break;

            default:
                $value = Operators::IS_EMPTY === $operator ? null : $this->formatValues($field, $value);
                $field = current($this->qb->getRootAliases()) . '.' . $field;
                $this->qb->andWhere($this->prepareCriteriaCondition($field, $operator, $value));
        }

        return $this;
    }

    /**
     * Add a filter for products not between $value[0] and $value[1] to the query builder
     *
     * @param string $field
     * @param array  $values Indexes must be datetime formatted to the self::DATETIME_FORMAT
     */
    protected function applyNotBetweenFilter($field, array $values)
    {
        $this->qb->andWhere(
            $this->qb->expr()->orX(
                $this->qb->expr()->lt($field, $this->qb->expr()->literal($values[0])),
                $this->qb->expr()->gt($field, $this->qb->expr()->literal($values[1]))
            )
        );
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

        $this->applyGreaterThanFilter($updatedField, $fromDate->format(static::DATETIME_FORMAT));
    }

    /**
     * Add a filter for products updated since the last export to the query builder
     *
     * @param string $field
     * @param string $value
     */
    protected function addUpdatedSinceLastExport($field, $value)
    {
        $jobInstance = $this->jobInstanceRepository->findOneBy(['code' => $value]);
        $lastCompletedJobExecution = $this->jobRepository->getLastJobExecution($jobInstance, BatchStatus::COMPLETED);
        if (null === $lastCompletedJobExecution) {
            return;
        }

        $lastJobStartTime = $lastCompletedJobExecution->getStartTime();
        $updatedField = current($this->qb->getRootAliases()) . '.' . $field;

        $this->applyGreaterThanFilter($updatedField, $lastJobStartTime->format(static::DATETIME_FORMAT));
    }

    /**
     * @param string $field
     * @param string $datetime
     */
    protected function applyGreaterThanFilter($field, $datetime)
    {
        $this->qb->andWhere(
            $this->qb->expr()->gt(
                $field,
                $this->qb->expr()->literal($datetime)
            )
        );
    }

    /**
     * Format values to string or array of strings
     *
     * @param string $type
     * @param mixed  $value
     *
     * @throws InvalidArgumentException
     *
     * @return mixed $value
     */
    protected function formatValues($type, $value)
    {
        if (is_array($value) && 2 !== count($value)) {
            throw InvalidArgumentException::expected(
                $type,
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                print_r($value, true)
            );
        }

        if (is_array($value)) {
            $tmpValues = [];
            foreach ($value as $tmp) {
                $tmpValues[] = $this->formatSingleValue($type, $tmp);
            }
            $value = $tmpValues;
        } else {
            $value = $this->formatSingleValue($type, $value);
        }

        return $value;
    }

    /**
     * @param string $type
     * @param mixed  $value
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function formatSingleValue($type, $value)
    {
        if (null === $value) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            $value->setTimezone(new \DateTimeZone('UTC'));
            return $value->format(static::DATETIME_FORMAT);
        }

        if (is_string($value)) {
            $dateTime = \DateTime::createFromFormat(static::DATETIME_FORMAT, $value);

            if (!$dateTime || 0 < $dateTime->getLastErrors()['warning_count']) {
                throw InvalidArgumentException::expected(
                    $type,
                    'a string with the format yyyy-mm-dd H:i:s',
                    'filter',
                    'date',
                    $value
                );
            }

            return $dateTime->format(static::DATETIME_FORMAT);
        }

        throw InvalidArgumentException::expected(
            $type,
            'array with 2 elements, string or \DateTime',
            'filter',
            'date',
            print_r($value, true)
        );
    }
}
