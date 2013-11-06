<?php

namespace Pim\Bundle\FlexibleEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * Base entity attribute localized
 *
 * @ORM\Table(name="pim_flexibleentity_attribute_translation", indexes={
 *      @ORM\Index(name="attribute_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class AttributeTranslation extends AbstractTranslation
{
    /**
     * @var int $foreignKey
     *
     * @ORM\Column(name="foreign_key", type="integer")
     */
    protected $foreignKey;

    /**
     * @var string $content
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $content;

    /**
     * Other required columns are mapped through inherited superclass
     */
}
