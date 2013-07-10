<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category translation entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity()
 * @ORM\Table(
 *     name="pim_category_translation",
 *     indexes={
 *         @ORM\Index(
 *             name="pim_category_translations_idx",
 *             columns={"locale", "object_class", "field", "foreign_key"}
 *         )
 *     }
 * )
 *
 */
class CategoryTranslation extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */

    /**
     * Change foreign key to add constraint and work with basic entity
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Model\CategoryInterface", inversedBy="translations")
     * @ORM\JoinColumn(name="foreign_key", referencedColumnName="id")
     */
    protected $foreignKey;
}
