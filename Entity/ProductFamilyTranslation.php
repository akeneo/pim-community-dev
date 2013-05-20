<?php

namespace Pim\Bundle\ProductBundle\Entity;

use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product family translation entity
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity()
 * @ORM\Table(
 *     name="pim_product_family_translation",
 *     indexes={
 *         @ORM\Index(
 *             name="pim_product_family_translations_idx",
 *             columns={"locale", "object_class", "field", "foreign_key"}
 *         )
 *     }
 * )
 *
 */
class ProductFamilyTranslation extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */

    /**
     * Change foreign key to add constraint and work with basic entity
     *
     * @ORM\ManyToOne(targetEntity="ProductFamily", inversedBy="translations")
     * @ORM\JoinColumn(name="foreign_key", referencedColumnName="id")
     */
    protected $foreignKey;
}
