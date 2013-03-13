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
     * Store upload file
     *
     * @var File $fileUpload
     */
    protected $fileUpload;

    /**
     * Get file uploaded
     *
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function getFileUpload()
    {
        return $this->fileUpload;
    }

    /**
     * Set file uploaded
     *
     * @param File $fileUpload
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductValue
     */
    public function setFileUpload(File $fileUpload)
    {
        $this->fileUpload = $fileUpload;

        return $this;
    }
}
