<?php

namespace Pim\Bundle\ImportExportBundle\Entity;

use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;

use Pim\Bundle\ImportExportBundle\Model\ExportInterface;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Export class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(
 *     "pim_export",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="pim_export_code_uc", columns={"code"})}
 * )
 * @ORM\Entity()
 * @UniqueEntity(fields="code", message="This code is already taken.")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 */
class Export implements ExportInterface, TranslatableInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=100, unique=true)
     * @Assert\Regex(pattern="/^[a-zA-Z0-9_-]+$/", message="The code must only contain alphanumeric characters.")
     */
    protected $code;

    /**
     * @var ArrayCollection $translations
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\ImportExportBundle\Entity\ExportTranslation",
     *     mappedBy="foreignKey",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    protected $translations;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_scheduled", type="boolean")
     */
    protected $scheduled;

    /**
     * @var integer
     *
     * @ORM\Column(name="content", type="integer")
     */
    protected $content;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    protected $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_export", type="datetime")
     */
    protected $lastExport;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="next_export", type="datetime")
     */
    protected $nextExport;

//     /**
//      * @var \stdClass
//      *
//      * @ORM\Column(name="job", type="object")
//      */
//     protected $job;

//     /**
//      * @var \stdClass
//      *
//      * @ORM\Column(name="connector", type="object")
//      */
//     protected $connector;

//     /**
//      * @var \stdClass
//      *
//      * @ORM\Column(name="channel", type="object")
//      */
//     protected $channel;

//     /**
//      * @var \stdClass
//      *
//      * @ORM\Column(name="locales", type="object")
//      */
//     protected $locales;

//     /**
//      * @var \stdClass
//      *
//      * @ORM\Column(name="tree", type="object")
//      */
//     protected $tree;

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
     * Set code
     *
     * @param string $code
     *
     * @return Export
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
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
     * Set scheduled
     *
     * @param boolean $scheduled
     *
     * @return Export
     */
    public function setScheduled($scheduled)
    {
        $this->scheduled = $scheduled;

        return $this;
    }

    /**
     * Predicate is scheduled
     *
     * @return boolean
     */
    public function isScheduled()
    {
        return $this->scheduled;
    }

    /**
     * Set content
     *
     * @param integer $content
     *
     * @return Export
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return integer
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Export
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set lastExport
     *
     * @param \DateTime $lastExport
     *
     * @return Export
     */
    public function setLastExport($lastExport)
    {
        $this->lastExport = $lastExport;

        return $this;
    }

    /**
     * Get lastExport
     *
     * @return \DateTime
     */
    public function getLastExport()
    {
        return $this->lastExport;
    }

    /**
     * Set nextExport
     *
     * @param \DateTime $nextExport
     *
     * @return Export
     */
    public function setNextExport($nextExport)
    {
        $this->nextExport = $nextExport;

        return $this;
    }

    /**
     * Get nextExport
     *
     * @return \DateTime
     */
    public function getNextExport()
    {
        return $this->nextExport;
    }

//     /**
//      * Set job
//      *
//      * @param \stdClass $job
//      * @return Export
//      */
//     public function setJob($job)
//     {
//         $this->job = $job;

//         return $this;
//     }

//     /**
//      * Get job
//      *
//      * @return \stdClass
//      */
//     public function getJob()
//     {
//         return $this->job;
//     }

//     /**
//      * Set connector
//      *
//      * @param \stdClass $connector
//      * @return Export
//      */
//     public function setConnector($connector)
//     {
//         $this->connector = $connector;

//         return $this;
//     }

//     /**
//      * Get connector
//      *
//      * @return \stdClass
//      */
//     public function getConnector()
//     {
//         return $this->connector;
//     }

//     /**
//      * Set channel
//      *
//      * @param \stdClass $channel
//      * @return Export
//      */
//     public function setChannel($channel)
//     {
//         $this->channel = $channel;

//         return $this;
//     }

//     /**
//      * Get channel
//      *
//      * @return \stdClass
//      */
//     public function getChannel()
//     {
//         return $this->channel;
//     }

//     /**
//      * Set locales
//      *
//      * @param \stdClass $locales
//      * @return Export
//      */
//     public function setLocales($locales)
//     {
//         $this->locales = $locales;

//         return $this;
//     }

//     /**
//      * Get locales
//      *
//      * @return \stdClass
//      */
//     public function getLocales()
//     {
//         return $this->locales;
//     }

//     /**
//      * Set tree
//      *
//      * @param \stdClass $tree
//      * @return Export
//      */
//     public function setTree($tree)
//     {
//         $this->tree = $tree;

//         return $this;
//     }

//     /**
//      * Get tree
//      *
//      * @return \stdClass
//      */
//     public function getTree()
//     {
//         return $this->tree;
//     }

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
        return 'Pim\Bundle\ImportExportBundle\Entity\ExportTranslation';
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        $translated = $this->getTranslation()->getLabel();

        return ($translated != '') ? $translated : $this->getTranslation(self::FALLBACK_LOCALE)->getLabel();
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return string
     */
    public function setLabel($label)
    {
        $translation = $this->getTranslation()->setLabel($label);

        return $this;
    }
}
