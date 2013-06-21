<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

use Oro\Bundle\AddressBundle\Entity\Region;

/**
 * @ORM\Table(name="oro_dictionary_region_translation", indexes={
 *      @ORM\Index(name="region_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class RegionTranslation extends AbstractTranslation
{
    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Region", inversedBy="translation")
     * @ORM\JoinColumn(name="region_code", referencedColumnName="combined_code", onDelete="CASCADE")
     **/
    private $region;

    /**
     * @var string $content
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $content;

    /**
     * @return Region
     */
    public function getRegion()
    {
        return $this->region;
    }
}
