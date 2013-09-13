<?php
namespace Oro\Bundle\TestFrameworkBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="test_search_item")
 * @ORM\Entity(repositoryClass="Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository")
 *
 */
class Item extends AbstractEntityFlexible
{

    /**
     * @var string $stringValue
     *
     * @ORM\Column(name="stringValue", type="string", nullable=true)
     */
    protected $stringValue;

    /**
     * @var integer $integerValue
     *
     * @ORM\Column(name="integerValue", type="integer", nullable=true)
     */
    protected $integerValue;

    /**
     * @var decimal $decimalValue
     *
     * @ORM\Column(name="decimalValue", type="decimal", scale=2, nullable=true)
     */
    protected $decimalValue;

    /**
     * @var float $floatValue
     *
     * @ORM\Column(name="floatValue", type="float", nullable=true)
     */
    protected $floatValue;

    /**
     * @var boolean $booleanValue
     *
     * @ORM\Column(name="booleanValue", type="boolean", nullable=true)
     */
    protected $booleanValue;

    /**
     * @var blob $blobValue
     *
     * @ORM\Column(name="blobValue", type="blob", nullable=true)
     */
    protected $blobValue;

    /**
     * @var array $arrayValue
     *
     * @ORM\Column(name="arrayValue", type="array", nullable=true)
     */
    protected $arrayValue;

    /**
     * @var datetime $arrayValue
     *
     * @ORM\Column(name="datetimeValue", type="datetime", nullable=true)
     */
    protected $datetimeValue;

    /**
     * @var guid $arrayValue
     *
     * @ORM\Column(name="guidValue", type="guid", nullable=true)
     */
    protected $guidValue;

    /**
     * @var object $objectValue
     *
     * @ORM\Column(name="objectValue", type="object", nullable=true)
     */
    protected $objectValue;

    /**
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="ItemValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __toString()
    {
        return (string)$this->stringValue;
    }
}
