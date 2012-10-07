<?php
namespace Akeneo\CatalogBundle\Entity\Product\Translation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * Custom translation entity for values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="AkeneoCatalog_Product_Value_Translation", indexes={
 *      @ORM\index(name="value_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class ValueTranslation extends AbstractTranslation
{

    // TODO : https://github.com/stof/StofDoctrineExtensionsBundle/issues/149

}