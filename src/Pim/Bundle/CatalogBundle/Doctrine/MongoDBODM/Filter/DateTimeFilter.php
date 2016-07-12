<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
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
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        JobInstanceRepository $jobInstanceRepository,
        JobRepositoryInterface $jobRepository,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields       = $supportedFields;
        $this->supportedOperators    = $supportedOperators;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobRepository         = $jobRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (Operators::IS_EMPTY !== $operator &&
            Operators::IS_NOT_EMPTY !== $operator &&
            Operators::SINCE_LAST_JOB !== $operator &&
            Operators::SINCE_LAST_N_DAYS !== $operator
        ) {
            $value = $this->formatValues($field, $value);
        }

        if (Operators::SINCE_LAST_JOB === $operator) {
            if (!is_string($value)) {
                throw InvalidArgumentException::stringExpected($field, 'filter', 'updated', gettype($value));
            }

            $jobInstance = $this->jobInstanceRepository->findOneBy(['code' => $value]);
            $lastCompletedJobExecution = $this->jobRepository->getLastJobExecution($jobInstance, BatchStatus::COMPLETED);
            if (null === $lastCompletedJobExecution) {
                return $this;
            }
            $lastJobStartTime = $lastCompletedJobExecution->getStartTime();
            $value            = $lastJobStartTime->format(static::DATETIME_FORMAT);
            $operator         = Operators::GREATER_THAN;
        }

        if (Operators::SINCE_LAST_N_DAYS === $operator) {
            if (!is_numeric($value)) {
                throw InvalidArgumentException::numericExpected($field, 'filter', 'updated', gettype($value));
            }

            $fromDate = new \DateTime(sprintf('%s days ago', $value), new \DateTimeZone('UTC'));
            $value    = $fromDate->format(static::DATETIME_FORMAT);
            $operator = Operators::GREATER_THAN;
        }

        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);

        $this->applyFilter($field, $operator, $value);

        return $this;
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param string $field
     * @param string $operator
     * @param mixed  $value
     */
    protected function applyFilter($field, $operator, $value)
    {
        switch ($operator) {
            case Operators::BETWEEN:
                $this->qb->field($field)->gte($value[0]);
                $this->qb->field($field)->lte($value[1]);
                break;
            case Operators::NOT_BETWEEN:
                $this->qb->addAnd(
                    $this->qb->expr()
                        ->addOr($this->qb->expr()->field($field)->lt($value[0]))
                        ->addOr($this->qb->expr()->field($field)->gt($value[1]))
                );
                break;
            case Operators::GREATER_THAN:
                $this->qb->field($field)->gt($value);
                break;
            case Operators::LOWER_THAN:
                $this->qb->field($field)->lt($value);
                break;
            case Operators::EQUALS:
                $this->qb->field($field)->equals($value);
                break;
            case Operators::NOT_EQUAL:
                $this->qb->field($field)->exists(true);
                $this->qb->field($field)->notEqual($value);
                break;
            case Operators::IS_EMPTY:
                $this->qb->field($field)->exists(false);
                break;
            case Operators::IS_NOT_EMPTY:
                $this->qb->field($field)->exists(true);
                break;
        }
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
     * @return integer
     */
    protected function formatSingleValue($type, $value)
    {
        if (null === $value) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            $value->setTimezone(new \DateTimeZone('UTC'));
            return $value->getTimestamp();
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

            return $dateTime->getTimestamp();
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
