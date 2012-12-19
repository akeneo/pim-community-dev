<?php
namespace Oro\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * Custom translation entity (and table) for products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="product_translations", indexes={
 *      @ORM\Index(name="translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 *
 */
class ProductTranslation extends AbstractTranslation
{

}
