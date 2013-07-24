<?php

namespace Oro\Bundle\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * EmailTemplate
 *
 * @ORM\Table(name="oro_email_template",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="UQ_NAME", columns={"name", "entityName"})},
 *      indexes={@ORM\Index(name="email_name_idx", columns={"name"}),
 *          @ORM\Index(name="email_is_system_idx", columns={"isSystem"}),
 *          @ORM\Index(name="email_entity_name_idx", columns={"entityName"})})
 * @ORM\Entity(repositoryClass="Oro\Bundle\NotificationBundle\Entity\Repository\EmailTemplateRepository")
 * @Gedmo\TranslationEntity(class="Oro\Bundle\NotificationBundle\Entity\EmailTemplateTranslation")*
 */
class EmailTemplate implements Translatable
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
     * @var boolean
     *
     * @ORM\Column(name="isSystem", type="boolean")
     */
    protected $isSystem;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Gedmo\Translatable
     */
    protected $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="parent", type="integer", nullable=true)
     */
    protected $parent;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     * @Gedmo\Translatable
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     * @Gedmo\Translatable
     */
    protected $content;

    /**
     * @var string
     *
     * @ORM\Column(name="entityName", type="string", length=255, nullable=true)
     */
    protected $entityName;

    /**
     * Template type:
     *  - html
     *  - text
     *
     * @ORM\Column(name="type", type="string", length=20)
     * @var string
     */
    protected $type;

    /**
     * @Gedmo\Locale
     */
    protected $locale;


    /**
     * @param $name
     * @param string $content
     * @param string $type
     * @param bool $isSystem
     * @internal param $entityName
     */
    public function __construct($name, $content = '', $type = 'html', $isSystem = true)
    {
        foreach (array('subject', 'entityName') as $templateParam) {
            if (preg_match('#@' . $templateParam . '\s?=\s?(.*)\n#si', $content, $match)) {
                $this->$templateParam = trim($match[1]);
                $content = trim(str_replace($match[0], '', $content));
            }
        }

        $this->name = $name;
        $this->content = $content;
        $this->type = $type;
        $this->isSystem = $isSystem;
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
     * Set name
     *
     * @param string $name
     * @return EmailTemplate
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set parent
     *
     * @param integer $parent
     * @return EmailTemplate
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return integer 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return EmailTemplate
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    
        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return EmailTemplate
     */
    public function setContent($content)
    {
        $this->content = $content;
    
        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set entityName
     *
     * @param string $entityName
     * @return EmailTemplate
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    
        return $this;
    }

    /**
     * Get entityName
     *
     * @return string 
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param boolean $isSystem
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = $isSystem;
    }

    /**
     * @return boolean
     */
    public function getIsSystem()
    {
        return $this->isSystem;
    }

    /**
     * @param mixed $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * clone template
     */
    public function __clone()
    {
        // cloned entity will be child
        $this->parent = $this->id;
        $this->id = null;
        $this->isSystem = false;
    }
}
