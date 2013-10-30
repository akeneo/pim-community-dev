<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\FlexibleEntityBundle\Entity\Media as BaseMedia;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Media
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_catalog_media")
 * @ORM\Entity
 */
class Media extends BaseMedia
{
    /**
     * @ORM\OneToOne(
     *     targetEntity="Pim\Bundle\CatalogBundle\Model\ProductValueInterface",
     *     inversedBy="media"
     * )
     * @ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $value;

    /**
     * Set the product value
     *
     * @param ProductValueInterface $value
     *
     * @return Media
     */
    public function setValue(ProductValueInterface $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the product value
     *
     * @return ProductValueInterface
     */
    public function getValue()
    {
        return $this->value;
    }
}
