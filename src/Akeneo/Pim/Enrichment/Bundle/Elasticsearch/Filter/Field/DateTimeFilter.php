<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * DateTime filter for an Elasticsearch query
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /** @var IdentifiableObjectRepositoryInterface */
    protected $jobInstanceRepository;

    /** @var JobRepositoryInterface */
    protected $jobRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepository
     * @param JobRepositoryInterface $jobRepository
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        JobRepositoryInterface $jobRepository,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobRepository = $jobRepository;
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

        $this->checkValue($operator, $field, $value);

        switch ($operator) {
            case Operators::EQUALS:
                $clause = [
                    'term' => [
                        $field => $this->getFormattedDate($field, $value)
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::LOWER_THAN:
                $clause = [
                    'range' => [
                        $field => ['lt' => $this->getFormattedDate($field, $value)]
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::GREATER_THAN:
                $clause = [
                    'range' => [
                        $field => ['gt' => $this->getFormattedDate($field, $value)]
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::BETWEEN:
                $values = array_values($value);
                $clause = [
                    'range' => [
                        $field => [
                            'gte' => $this->getFormattedDate($field, $values[0]),
                            'lte' => $this->getFormattedDate($field, $values[1])
                        ]
                    ]
                ];

                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::NOT_BETWEEN:
                $values = array_values($value);
                $betweenClause = [
                    'range' => [
                        $field => [
                            'gte' => $this->getFormattedDate($field, $values[0]),
                            'lte' => $this->getFormattedDate($field, $values[1])
                        ]
                    ]
                ];

                $this->searchQueryBuilder->addMustNot($betweenClause);
                $this->searchQueryBuilder->addFilter($this->getExistsClause($field));

                break;
            case Operators::IS_EMPTY:
                $this->searchQueryBuilder->addMustNot($this->getExistsClause($field));

                break;
            case Operators::IS_NOT_EMPTY:
                $this->searchQueryBuilder->addFilter($this->getExistsClause($field));

                break;
            case Operators::NOT_EQUAL:
                $mustNotClause = [
                    'term' => [
                        $field => $this->getFormattedDate($field, $value)
                    ]
                ];

                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($this->getExistsClause($field));

                break;
            case Operators::SINCE_LAST_N_DAYS:
                return $this->addFieldFilter(
                    $field,
                    Operators::GREATER_THAN,
                    new \DateTime(sprintf('%s days ago', $value), new \DateTimeZone('UTC')),
                    $locale,
                    $channel,
                    $options
                );
            case Operators::SINCE_LAST_JOB:
                $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($value);

                if (null === $jobInstance) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        'job_instance',
                        'code',
                        'The job instance does not exist',
                        static::class,
                        $value
                    );
                }

                $lastCompletedJobExecution = $this->jobRepository->getLastJobExecution($jobInstance, BatchStatus::COMPLETED);
                if (null === $lastCompletedJobExecution) {
                    return $this;
                }

                return $this->addFieldFilter(
                    $field,
                    Operators::GREATER_THAN,
                    $lastCompletedJobExecution->getStartTime()->setTimezone(new \DateTimeZone('UTC')),
                    $locale,
                    $channel,
                    $options
                );
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * @param string $field
     *
     * @return array
     */
    protected function getExistsClause($field)
    {
        return [
            'exists' => ['field' => $field]
        ];
    }

    /**
     * @param string $operator
     * @param string $field
     * @param string|array|\DateTime $value
     */
    protected function checkValue($operator, $field, $value)
    {
        switch ($operator) {
            case Operators::EQUALS:
            case Operators::LOWER_THAN:
            case Operators::GREATER_THAN:
            case Operators::NOT_EQUAL:
                FieldFilterHelper::checkDateTime(
                    $field,
                    $value,
                    static::DATETIME_FORMAT,
                    'yyyy-mm-dd H:i:s',
                    static::class
                );

                break;
            case Operators::BETWEEN:
            case Operators::NOT_BETWEEN:
                if (!is_array($value)) {
                    throw InvalidPropertyTypeException::arrayExpected($field, static::class, $value);
                }

                if (2 !== count($value)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $field,
                        sprintf('should contain 2 strings with the format "%s"', "yyyy-mm-dd H:i:s"),
                        static::class,
                        $value
                    );
                }

                foreach ($value as $singleValue) {
                    FieldFilterHelper::checkDateTime(
                        $field,
                        $singleValue,
                        static::DATETIME_FORMAT,
                        'yyyy-mm-dd H:i:s',
                        static::class
                    );
                }

                break;
            case Operators::SINCE_LAST_JOB:
                if (!is_string($value)) {
                    throw InvalidPropertyTypeException::stringExpected($field, static::class, $value);
                }

                break;
            case Operators::SINCE_LAST_N_DAYS:
                if (!is_numeric($value)) {
                    throw InvalidPropertyTypeException::numericExpected($field, static::class, $value);
                }

                break;
            case Operators::IS_EMPTY:
            case Operators::IS_NOT_EMPTY:
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }
    }

    /**
     * @param string $field
     * @param string|\DateTime $value
     *
     * @return string
     */
    protected function getFormattedDate($field, $value)
    {
        $dateTime = $value;
        $utcTimeZone = new \DateTimeZone('UTC');

        if (!$dateTime instanceof \DateTime) {
            $dateTime = \DateTime::createFromFormat(static::DATETIME_FORMAT, $dateTime, $utcTimeZone);

            if (false === $dateTime || 0 < $dateTime->getLastErrors()['warning_count']) {
                throw InvalidPropertyException::dateExpected(
                    $field,
                    static::DATETIME_FORMAT,
                    static::class,
                    $value
                );
            }
        }

        $dateTime->setTimezone($utcTimeZone);

        return $dateTime->format('c');
    }
}
