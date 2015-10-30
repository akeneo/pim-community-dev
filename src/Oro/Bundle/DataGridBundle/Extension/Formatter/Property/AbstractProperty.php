<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

abstract class AbstractProperty implements PropertyInterface
{
    /** @var array */
    protected $params;

    /**
     * Map configuration keys to metadata keys
     *
     * @var array
     */
    protected $paramMap = [
        self::FRONTEND_TYPE_KEY => self::METADATA_TYPE_KEY
    ];

    /** @var array */
    protected $excludeParamsDefault = [self::TYPE_KEY, self::DATA_NAME_KEY];

    /** @var array */
    protected $excludeParams = [];

    /**
     * {@inheritdoc}
     */
    final public function init(PropertyConfiguration $params)
    {
        $this->params = $params;
        $this->initialize();

        return $this;
    }

    /**
     * Override this method instead "init" in case when we want to customize something
     */
    protected function initialize()
    {
    }

    /**
     * @param ResultRecordInterface $record
     *
     * @return mixed
     */
    abstract protected function getRawValue(ResultRecordInterface $record);

    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        return $this->format($this->getRawValue($record));
    }

    /**
     * Convert value to appropriate type
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function convertValue($value)
    {
        switch ($this->getOr(self::FRONTEND_TYPE_KEY)) {
            case self::TYPE_DATETIME:
            case self::TYPE_DATE:
                if ($value instanceof \DateTime) {
                    $value = $value->format(\DateTime::ISO8601);
                }
                $result = (string)$value;
                break;
            case self::TYPE_STRING:
                $result = (string)$value;
                break;
            case self::TYPE_DECIMAL:
                $result = floatval($value);
                break;
            case self::TYPE_INTEGER:
                $result = intval($value);
                break;
            case self::TYPE_BOOLEAN:
                $result = (bool)$value;
                break;
            default:
                $result = $value;
        }

        return $result;
    }


    /**
     * Format raw value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function format($value)
    {
        if (null === $value) {
            return $value;
        }

        $result = $this->convertValue($value);

        if (is_object($result) && is_callable([$result, '__toString'])) {
            $result = (string)$result;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $defaultMetadata = [
            // use field name if label not set
            'label' => ucfirst($this->get('name')),
        ];

        $metadata = $this->get()->toArray([], array_merge($this->excludeParams, $this->excludeParamsDefault));
        $metadata = $this->mapParams($metadata);
        $metadata = array_merge($defaultMetadata, $this->guessAdditionalMetadata(), $metadata);

        return $metadata;
    }

    /**
     * Get param or throws exception
     *
     * @param string $paramName
     *
     * @throws \LogicException
     * @return PropertyConfiguration|mixed
     */
    protected function get($paramName = null)
    {
        $value = $this->params;

        if ($paramName !== null) {
            if (!isset($this->params[$paramName])) {
                throw new \LogicException(sprintf('Trying to access not existing parameter: "%s"', $paramName));
            }

            $value = $this->params[$paramName];
        }

        return $value;
    }

    /**
     * Get param if exists or default value
     *
     * @param string $paramName
     * @param null   $default
     *
     * @return mixed
     */
    protected function getOr($paramName = null, $default = null)
    {
        if ($paramName !== null) {
            return isset($this->params[$paramName]) ? $this->params[$paramName] : $default;
        }

        return $this->params;
    }

    /**
     * Process mapping params
     *
     * @param array $params
     *
     * @return array
     */
    protected function mapParams($params)
    {
        $keys = [];
        foreach (array_keys($params) as $key) {
            if (isset($this->paramMap[$key])) {
                $keys[] = $this->paramMap[$key];
            } else {
                $keys[] = $key;
            }
        }

        return array_combine($keys, array_values($params));
    }

    /**
     * Guess additional metadata dependent on frontend type
     *
     * @return array
     */
    protected function guessAdditionalMetadata()
    {
        $metadata = [];

        switch ($this->getOr(self::FRONTEND_TYPE_KEY)) {
            case self::TYPE_INTEGER:
                $metadata = ['style' => 'integer'];
                break;
            case self::TYPE_DECIMAL:
                $metadata = ['style' => 'decimal'];
                break;
            case self::TYPE_PERCENT:
                $metadata = ['style' => 'percent'];
                break;
            case self::TYPE_BOOLEAN:
                $metadata = ['width' => 10];
                break;
        }

        return $metadata;
    }
}
