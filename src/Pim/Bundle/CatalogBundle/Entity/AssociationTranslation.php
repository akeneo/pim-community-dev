<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

/**
 * Association translation entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity()
 * @ORM\Table(name="pim_catalog_association_translation")
 */
class AssociationTranslation extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */

    /**
     * Change foreign key to add constraint and work with basic entity
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\CatalogBundle\Entity\Association", inversedBy="translations")
     * @ORM\JoinColumn(name="foreign_key", referencedColumnName="id")
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
     * @return AssociationTranslation
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}
