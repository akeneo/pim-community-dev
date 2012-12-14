<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * Custom translation entity for values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * ORM\Table(name="akeneo_catalog_product_translation", indexes={
 *      ORM\index(name="translation_idx", columns={"locale", "object_class", "attribute", "foreign_key"})
 * })
 * ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 *
 * @ORM\Table(name="akeneo_catalog_product_translation")
 * @ORM\Entity()
 */
class ProductTranslation extends AbstractTranslation
{

    // TODO : https://github.com/stof/StofDoctrineExtensionsBundle/issues/149

}
