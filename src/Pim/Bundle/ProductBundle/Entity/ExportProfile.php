<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

/**
 * Export profile entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity()
 * @ORM\Table(name="pim_export_profile")
 * @UniqueEntity("code")
 */
class ExportProfile implements TranslatableInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=100)
     */
    protected $code;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string $locale
     */
    protected $locale = self::FALLBACK_LOCALE;

    /**
     * @var ArrayCollection $translations
     *
     * @ORM\OneToMany(
     *     targetEntity="ExportProfileTranslation",
     *     mappedBy="foreignKey",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ExportProfile
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslation($locale = null)
    {
        $locale = ($locale) ? $locale : $this->locale;
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() == $locale) {
                return $translation;
            }
        }

        $translationClass = $this->getTranslationFQCN();
        $translation      = new $translationClass();
        $translation->setLocale($locale);
        $translation->setForeignKey($this);
        $this->addTranslation($translation);

        return $translation;
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(AbstractTranslation $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(AbstractTranslation $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationFQCN()
    {
        return 'Pim\Bundle\ProductBundle\Entity\ExportProfileTranslation';
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        $translated = $this->getTranslation()->getName();

        return ($translated != '') ? $translated : $this->getTranslation(self::FALLBACK_LOCALE)->getName();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    public function setName($name)
    {
        $this->getTranslation()->setName($name);

        return $this;
    }
}
