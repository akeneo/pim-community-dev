<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Symfony\Component\HttpFoundation\File\File;

use Doctrine\ORM\Mapping as ORM;

/**
 * Media entity
 * File is not save here
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_media")
 * @ORM\Entity
 */
class Media
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
     * File uploaded in form
     * @var \Symfony\Component\HttpFoundation\File\File $file
     */
    protected $file;

    /**
     * Filepath where file is located
     * @var string $filepath
     *
     * @ORM\Column(name="filepath", type="string", length=255, unique=true, nullable=true)
     */
    protected $filepath;

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
     * Set id
     *
     * @param integer $id
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Media
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get file
     *
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set file
     *
     * @param \Symfony\Component\HttpFoundation\File\File $file
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Media
     */
    public function setFile(File $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get filepath
     *
     * @return string
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * Set filepath
     *
     * @param string $filepath
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Media
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
    }
}
