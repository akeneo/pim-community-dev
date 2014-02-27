<?php

namespace Pim\Bundle\FlexibleEntityBundle\Entity;

use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Base entity attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(
 *     name="pim_flexibleentity_attribute", indexes={@ORM\Index(name="searchcode_idx", columns={"code"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="searchunique_idx", columns={"code", "entity_type"})}
 * )
 * @ORM\Entity(repositoryClass="Pim\Bundle\FlexibleEntityBundle\Entity\Repository\AttributeRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("code")
 * @Gedmo\TranslationEntity(class="Pim\Bundle\FlexibleEntityBundle\Entity\AttributeTranslation")
 */
class Attribute extends AbstractEntityAttribute
{
    /**
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=255)
     * @Gedmo\Translatable
     */
    protected $label;

    /**
     * Overrided to change target entity name
     *
     * @var ArrayCollection $options
     *
     * @ORM\OneToMany(
     *     targetEntity="AttributeOption", mappedBy="attribute", cascade={"persist", "remove"}, orphanRemoval=true
     * )
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $options;

    /**
     * @ORM\Column(name="sort_order", type="integer")
     */
    protected $sortOrder = 0;

    /**
     * Convert defaultValue to UNIX timestamp if it is a DateTime object
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function convertDefaultValueToTimestamp()
    {
        if ($this->getDefaultValue() instanceof \DateTime) {
            $this->setDefaultValue($this->getDefaultValue()->format('U'));
        }
    }

    /**
     * Convert defaultValue to DateTime if attribute type is date
     *
     * @ORM\PostLoad
     */
    public function convertDefaultValueToDatetime()
    {
        if ($this->getDefaultValue()) {
            // TODO : must be moved and avoid to use service name here
            if ($this->getAttributeType() === 'pim_flexibleentity_date') {
                $date = new \DateTime('now', new \DateTimeZone('UTC'));
                $date->setTimestamp(intval($this->getDefaultValue()));

                $this->setDefaultValue($date);
            }
        }
    }

    /**
     * Convert defaultValue to integer if attribute type is boolean
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function convertDefaultValueToInteger()
    {
        if ($this->getDefaultValue() !== null) {
            // TODO : must be moved and avoid to use service name here
            if ($this->getAttributeType() === 'pim_flexibleentity_integer') {
                $this->setDefaultValue((int) $this->getDefaultValue());
            }
        }
    }

    /**
     * Convert defaultValue to boolean if attribute type is boolean
     *
     * @ORM\PostLoad
     */
    public function convertDefaultValueToBoolean()
    {
        if ($this->getDefaultValue() !== null) {
            // TODO : must be moved and avoid to use service name here
            if ($this->getAttributeType() === 'pim_flexibleentity_boolean') {
                $this->setDefaultValue((bool) $this->getDefaultValue());
            }
        }
    }

    /**
     * Get sort order
     * @return number
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set sort order
     *
     * @param integer $sortOrder
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
