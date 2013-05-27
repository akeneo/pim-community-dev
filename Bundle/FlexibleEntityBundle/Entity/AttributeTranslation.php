<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * Base entity attribute localized
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="oro_flexibleentity_attribute_translation", indexes={
 *      @ORM\Index(name="attribute_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class AttributeTranslation extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
