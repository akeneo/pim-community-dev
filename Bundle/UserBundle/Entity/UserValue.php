<?php

namespace Oro\Bundle\UserBundle\Entity;

use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

/**
 * @ORM\Table(name="oro_user_value")
 * @ORM\Entity
 * @Oro\Loggable
 */
class UserValue extends AbstractEntityFlexibleValue
{
    /**
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $attribute;

    /**
     * @var User $entity
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="values")
     */
    protected $entity;

    /**
     * Store values data when backend is option (deal to select, multi-select)
     *
     * @var Collection $options
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption")
     * @ORM\JoinTable(
     *     name="oro_user_value_option",
     *     joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $options;

    /**
     * Store varchar value
     *
     * @var string $varchar
     *
     * @ORM\Column(name="value_string", type="string", length=255, nullable=true)
     * @Oro\Versioned
     */
    protected $varchar;

    /**
     * Store int value
     *
     * @var integer $integer
     *
     * @ORM\Column(name="value_integer", type="integer", nullable=true)
     * @Oro\Versioned
     */
    protected $integer;

    /**
     * Store decimal value
     *
     * @var double $decimal
     *
     * @ORM\Column(name="value_decimal", type="decimal", nullable=true)
     * @Oro\Versioned
     */
    protected $decimal;

    /**
     * Store text value
     *
     * @var string $text
     *
     * @ORM\Column(name="value_text", type="text", nullable=true)
     * @Oro\Versioned
     */
    protected $text;

    /**
     * Store date value
     *
     * @var \DateTime $date
     *
     * @ORM\Column(name="value_date", type="date", nullable=true)
     * @Oro\Versioned
     */
    protected $date;

    /**
     * Store datetime value
     *
     * @var \DateTime $datetime
     *
     * @ORM\Column(name="value_datetime", type="datetime", nullable=true)
     * @Oro\Versioned
     */
    protected $datetime;
}
