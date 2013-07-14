<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractConfig
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
     * @var ConfigValue[]|ArrayCollection
     */
    protected $values;

    /**
     * @param ConfigValue[] $values
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
     * @param ConfigValue $value
     * @return $this
     */
    public function addValue($value)
    {
        $this->values->add($value);

        return $this;
    }

    /**
     * @param  callable $filter
     * @return array|ArrayCollection|ConfigValue[]
     */
    public function getValues(\Closure $filter = null)
    {
        return $filter ? array_filter($this->values->toArray(), $filter) : $this->values;
    }

    /**
     * @param $code
     * @param $scope
     * @return ConfigValue
     */
    public function getValue($code, $scope)
    {
        $values = $this->values->filter(function (ConfigValue $value) use ($code, $scope) {
            return ($value->getScope() == $scope && $value->getCode() == $code);
        });

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
     */
    public function fromArray($scope, array $values)
    {
        foreach ($values as $code => $value) {
            if (is_bool($value)) {
                $value = (int)$value;
            }

            if ($configValue = $this->getValue($code, $scope)) {
                $configValue->setValue($value);
            } else {
                $configValue = new ConfigValue($code, $scope, $value);

                if ($this instanceof ConfigEntity) {
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
        $values = $this->getValues(function (ConfigValue $value) use ($scope) {
            return $value->getScope() == $scope;
        });

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
