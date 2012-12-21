<?php
namespace Oro\Bundle\DataModelBundle\Entity;

use Oro\Bundle\DataModelBundle\Model\AbstractEntity;
use Oro\Bundle\DataModelBundle\Model\AbstractEntityAttribute;
use Oro\Bundle\DataModelBundle\Model\AbstractEntityAttributeValue;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @ORM\ManyToOne(targetEntity="EntityAttribute")
     */
    protected $attribute;

    /**
     * @var Entity $entity
     *
     * @ORM\ManyToOne(targetEntity="Entity", inversedBy="values")
     */
    protected $entity;

    /**
     * Store varchar value
     * @var string $stringvalue
     *
     * @ORM\Column(name="string_value", type="string", length=255, nullable=true)
     * @Gedmo\Translatable
     */
    protected $stringValue;

    /**
     * Store int value
     * @var integer $numbervalue
     *
     * @ORM\Column(name="number_value", type="integer", nullable=true)
     */
    protected $numberValue;


    /**
     * Store text value
     * @var integer $numbervalue
     *
     * @ORM\Column(name="text_value", type="text", nullable=true)
     * @Gedmo\Translatable
     */
    protected $textValue;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped attribute of entity metadata, just a simple property
     */
    protected $locale;

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
     * Return relevant backend value
     *
     * @param string $type
     *
     * @return string
     */
    public function getAttributeTypeToBackend($type)
    {
        // TODO how to dynamically add our own type and backend
        switch ($type) {
            case AbstractEntityAttribute::TYPE_STRING:
                return 'stringValue';
            case AbstractEntityAttribute::TYPE_TEXT:
                return 'textValue';
            case AbstractEntityAttribute::TYPE_NUMBER:
                return 'numberValue';
            default:
                throw new \Exception(sprintf('This attribute type %s is unknown', $type));
        }
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
        $backend = $this->getAttributeTypeToBackend($this->attribute->getType());
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
        $backend = $this->getAttributeTypeToBackend($this->attribute->getType());

        return $this->$backend;
    }
}
