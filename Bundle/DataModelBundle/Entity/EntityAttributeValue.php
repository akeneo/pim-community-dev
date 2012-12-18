<?php
namespace Oro\Bundle\DataModelBundle\Entity;

use Oro\Bundle\DataModelBundle\Model\Entity as AbstractEntity;
use Oro\Bundle\DataModelBundle\Model\EntityAttributeValue as AbstractEntityAttributeValue;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Base Doctrine ORM entity attribute value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT
 *
 */
abstract class EntityAttributeValue extends AbstractEntityAttributeValue
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
     * @ORM\Column(name="string_value", type="string", length=255)
     * @Gedmo\Translatable
     */
    protected $stringValue;

    /**
     * Store int value
     * @var integer $numbervalue
     *
     * @ORM\Column(name="number_value", type="integer")
     */
    protected $numberValue;


    /**
     * Store text value
     * @var integer $numbervalue
     *
     * @ORM\Column(name="text_value", type="text")
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
}
