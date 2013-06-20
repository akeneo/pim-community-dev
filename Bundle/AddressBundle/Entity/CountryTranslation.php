<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

use Oro\Bundle\AddressBundle\Entity\Country;

/**
 * @ORM\Table(name="oro_dictionary_country_translation", indexes={
 *      @ORM\Index(name="country_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class CountryTranslation extends AbstractTranslation
{
    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Country", inversedBy="translation")
     * @ORM\JoinColumn(name="country_code", referencedColumnName="iso2_code", onDelete="CASCADE")
     **/
    private $country;

    /**
     * @var string $content
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $content;

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }
}
