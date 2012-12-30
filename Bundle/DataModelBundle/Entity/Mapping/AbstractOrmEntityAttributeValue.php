<?php
namespace Oro\Bundle\DataModelBundle\Entity\Mapping;

use Oro\Bundle\DataModelBundle\Model\Entity\AbstractEntity;
use Oro\Bundle\DataModelBundle\Model\Entity\AbstractEntityAttribute;
use Oro\Bundle\DataModelBundle\Model\Entity\AbstractEntityAttributeValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base Doctrine ORM entity attribute value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 *
 */
abstract class AbstractOrmEntityAttributeValue extends AbstractEntityAttributeValue
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="AbstractOrmEntityAttribute")
     */
    protected $attribute;

    /**
     * @var Entity $entity
     *
     * @ORM\ManyToOne(targetEntity="AbstractOrmEntity", inversedBy="values")
     */
    protected $entity;

    /**
     * Locale scope TODO on 2 chars or 5 ?
     * @var string $localeCode
     *
     * @ORM\Column(name="locale", type="string", length=5, nullable=false)
     */
    protected $localeCode;

    /**
     * Store varchar value
     * @var string $varchar
     *
     * @ORM\Column(name="value_string", type="string", length=255, nullable=true)
     */
    protected $varchar;

    /**
     * Store int value
     * @var integer $integer
     *
     * @ORM\Column(name="value_integer", type="integer", nullable=true)
     */
    protected $integer;

    /**
     * Store decimal value
     * @var double $decimal
     *
     * @ORM\Column(name="value_decimal", type="decimal", nullable=true)
     */
    protected $decimal;

    /**
     * Store text value
     * @var string $text
     *
     * @ORM\Column(name="value_text", type="text", nullable=true)
     */
    protected $text;

    /**
     * Store date value
     * @var date $date
     *
     * @ORM\Column(name="value_date", type="date", nullable=true)
     */
    protected $date;

    /**
     * Store datetime value
     * @var string $datetime
     *
     * @ORM\Column(name="value_datetime", type="datetime", nullable=true)
     */
    protected $datetime;

    /**
     * Store option
     *
     * @var AbstractOrmEntityAttributeOption $option
     *
     * @ORM\ManyToOne(targetEntity="AbstractOrmEntityAttributeOption", inversedBy="attributeValues")
     */
    protected $option;

    /**
     * Set entity
     *
     * @param AbstractEntity $entity
     *
     * @return EntityAttributeValue
     */
    public function setEntity(AbstractEntity $entity = null)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Set data
     *
     * @param mixed $data
     *
     * @return EntityAttributeValue
     */
    public function setData($data)
    {
        $backend = $this->attribute->getBackendType();
        $this->$backend = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        $backend = $this->attribute->getBackendType();

        return $this->$backend;
    }

    /**
     * Set related option
     *
     * @param OrmEntityAttributeOption $option
     */
    public function setOption(OrmEntityAttributeOption $option)
    {
        $this->option = $option;
    }

    /**
     * Get related option
     *
     * @return OrmEntityAttributeOption
     */
    public function getOption()
    {
        return $this->option;
    }

}
