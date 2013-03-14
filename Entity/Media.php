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
     *
     * @var \Symfony\Component\HttpFoundation\File\File $file
     */
    protected $file;

    /**
     * Filename
     *
     * @var string $filename
     *
     * @ORM\Column(name="filename", type="string", length=255, unique=true)
     */
    protected $filename;

    /**
     * File path
     *
     * @var string $filePath
     *
     * @ORM\Column(name="filepath", type="string", length=255, unique=true)
     */
    protected $filePath;

    /**
     * Mime type
     *
     * @var string $mimeType
     *
     * @ORM\Column(name="mimeType", type="string", length=255)
     */
    protected $mimeType;

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
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Media
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get file path
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Set file path
     *
     * @param string $filePath
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Media
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Get mime type
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set mime type
     *
     * @param string $mimeType
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Media
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }
}
