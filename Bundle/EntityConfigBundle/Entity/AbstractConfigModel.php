<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractConfigModel
{
    /**
     * @var \DateTime $created
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \DateTime $updated
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;

    /**
     * @var string $updated
     * @ORM\Column(type="string", length=8)
     */
    protected $mode;

    /**
     * @var ConfigModelValue[]|ArrayCollection
     */
    protected $values;

    /**
     * @param ConfigModelValue[] $values
     * @return $this
     */
    public function setValues($values)
    {
        $this->values->clear();

        foreach ($values as $value) {
            $this->addValue($value);
        }

        return $this;
    }

    /**
     * @param ConfigModelValue $value
     * @return $this
     */
    public function addValue($value)
    {
        $this->values->add($value);

        return $this;
    }

    /**
     * @param string $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param  callable $filter
     * @return array|ArrayCollection|ConfigModelValue[]
     */
    public function getValues(\Closure $filter = null)
    {
        return $filter ? $this->values->filter($filter) : $this->values;
    }

    /**
     * @param $code
     * @param $scope
     * @return ConfigModelValue
     */
    public function getValue($code, $scope)
    {
        $values = $this->getValues(
            function (ConfigModelValue $value) use ($code, $scope) {
                return ($value->getScope() == $scope && $value->getCode() == $code);
            }
        );

        return $values->first();
    }

    /**
     * @param \DateTime $created
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $updated
     * @return $this
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param       $scope
     * @param array $values
     * @param array $serializableValues
     */
    public function fromArray($scope, array $values, array $serializableValues = array())
    {
        foreach ($values as $code => $value) {
            $serializable = isset($serializableValues[$code]) && (bool)$serializableValues[$code];

            if (!$serializable && is_bool($value)) {
                $value = (int)$value;
            }

            if (!$serializable && !is_string($value)) {
                $value = (string)$value;
            }

            if ($configValue = $this->getValue($code, $scope)) {
                $configValue->setValue($value);
            } else {

                $configValue = new ConfigModelValue($code, $scope, $value, $serializable);

                if ($this instanceof EntityConfigModel) {
                    $configValue->setEntity($this);
                } else {
                    $configValue->setField($this);
                }

                $this->addValue($configValue);
            }
        }
    }

    /**
     * @param $scope
     * @return array
     */
    public function toArray($scope)
    {
        $values = $this->getValues(
            function (ConfigModelValue $value) use ($scope) {
                return $value->getScope() == $scope;
            }
        );

        $result = array();
        foreach ($values as $value) {
            $result[$value->getCode()] = $value->getValue();
        }

        return $result;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->created = $this->updated = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updated = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
