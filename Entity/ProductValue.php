<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Symfony\Component\HttpFoundation\File\File;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Value for a product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_product_value")
 * @ORM\Entity
 */
class ProductValue extends AbstractEntityFlexibleValue
{
    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Entity\Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $attribute;

    /**
     * @var Product $entity
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="values")
     */
    protected $entity;

    /**
     * Store options values
     *
     * @var options ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption")
     * @ORM\JoinTable(name="acmedemoflexibleentity_product_value_option",
     *      joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $options;

    /**
     * Store upload values
     *
     * @var Media $media
     *
     * @ORM\OneToOne(targetEntity="Pim\Bundle\ProductBundle\Entity\Media", cascade="persist")
     * @ORM\JoinTable(name="pim_media")
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id")
     */
    protected $media;

    /**
     * Get media
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set media
     *
     * @param \Pim\Bundle\ProductBundle\Entity\Media $media
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductValue
     */
    public function setMedia($media)
    {
        $this->media = $media;

        return $this;
    }
}
