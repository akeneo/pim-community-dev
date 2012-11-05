<?php
namespace Pim\Bundle\IcecatConnectorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 *
 * Icecat connector import
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : Must be set in lower level to be used by all connectors
 *
 * @ORM\Table(name="PimConnectorIcecat_Import")
 * @ORM\Entity
 */
class Import
{
    /**
     * @staticvar string
     */
    const IMPORT_LANGUAGES  = 'import-languages';
    const IMPORT_PRODUCT    = 'import-product';
    const IMPORT_PRODUCTS   = 'import-products';
    const IMPORT_SUPPLIERS  = 'import-suppliers';

   /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=30)
     */
    protected $label;

    /**
     * @var string $result
     *
     * @ORM\Column(name="result", type="boolean")
     */
    protected $result;

    /**
     * @var datetime $importedAt
     *
     * @ORM\Column(name="importedAt", type="datetime")
     */
    protected $importedAt;

    /**
     * @var string $errorMessage
     *
     * @ORM\Column(name="error_message", type="string", length=255, nullable=true)
     */
    protected $errorMessage;

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
     * Set label
     *
     * @param string $label
     * @return Import
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set result
     *
     * @param boolean $result
     * @return Import
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result
     *
     * @return boolean
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set importedAt
     *
     * @param \DateTime $importedAt
     * @return Import
     */
    public function setImportedAt($importedAt)
    {
        $this->importedAt = $importedAt;

        return $this;
    }

    /**
     * Get importedAt
     *
     * @return \DateTime
     */
    public function getImportedAt()
    {
        return $this->importedAt;
    }

    /**
     * Set errorMessage
     *
     * @param string $errorMessage
     * @return Import
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * Get errorMessage
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}