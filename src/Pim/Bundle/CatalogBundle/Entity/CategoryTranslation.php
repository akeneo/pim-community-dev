<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

/**
 * Category translation entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity()
 * @ORM\Table(
 *      name="pim_catalog_category_translation",
 *      uniqueConstraints= {
 *          @ORM\UniqueConstraint(name="locale_foreign_key_idx", columns={"locale", "foreign_key"})
 *      }
 * )
 *
 * @ExclusionPolicy("all")
 */
class CategoryTranslation extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */

    /**
     * Change foreign key to add constraint and work with basic entity
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Model\CategoryInterface", inversedBy="translations")
     * @ORM\JoinColumn(name="foreign_key", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $foreignKey;

    /**
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=64, nullable=true)
     */
    protected $label;

    /**
     * Set label
     *
     * @param string $label
     *
     * @return AbstractTranslation
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string $label
     */
    public function getLabel()
    {
        return $this->label;
    }
}
