<?php

namespace Pim\Bundle\ImportExportBundle\Entity;

use Pim\Bundle\ConfigBundle\Entity\Locale;
use Pim\Bundle\ImportExportBundle\Model\ExportInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Export attribute have single table inheritance with Export entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity()
 */
class ExportAttribute extends Export implements ExportInterface
{
    /**
     * @var ArrayCollection $locales
     *
     * @ORM\ManyToMany(
     *     targetEntity="Pim\Bundle\ConfigBundle\Entity\Locale", cascade={"persist"}
     * )
     * @ORM\JoinTable(
     *     name="pim_export_att_locale",
     *     joinColumns={@ORM\JoinColumn(name="export_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="locale_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $locales;

    /**
     * Get locales
     *
     * @return ArrayCollection
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Add locale
     *
     * @param Locale $locale
     *
     * @return ExportAttribute
     */
    public function addLocale(Locale $locale)
    {
        $this->locales[] = $locale;

        return $this;
    }

    /**
     * Remove locale
     *
     * @param Locale $locale
     *
     * @return ExportAttribute
     */
    public function removeLocale(Locale $locale)
    {
        $this->locales->removeElement($locale);

        return $this;
    }

    /**
     * Set locales
     *
     * @param ArrayCollection $locales
     *
     * @return ExportAttribute
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;

        return $this;
    }
}
