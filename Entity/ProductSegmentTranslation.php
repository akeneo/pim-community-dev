<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

use Gedmo\Mapping\Annotation as Gedmo;

use Doctrine\ORM\Mapping as ORM;

/**
 * Flexible product segment translation entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 * @ORM\Table(
 *     name="pim_product_segment_translations",
 *     indexes={
 *         @ORM\Index(
 *             name="pim_product_segment_translations_idx",
 *             columns={"locale", "object_class", "field", "foreign_key"}
 *         )
 *     }
 * )
 *
 */
class ProductSegmentTranslation extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}