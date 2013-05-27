<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="oro_dictionary_region_localized", indexes={
 *      @ORM\Index(name="region_localized_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class RegionLocalized extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
