<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * Base connector config
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="Akeneo_PimConnectorIcecat_Config")
 * @ORM\Entity
 */
class Config
{
    /**
     * @staticvar string
     */
    const LANGUAGES_URL  = 'languages-url';
    const LANGUAGES_FILE = 'languages-file';
    const LANGUAGES_ARCHIVED_FILE = 'languages-archived-file';

    const PRODUCTS_URL   = 'products-url';
    const PRODUCTS_FILE  = 'products-file';
    const PRODUCTS_ARCHIVED_FILE = 'products-archived-file';

    const PRODUCT_URL    = 'product-url';
    const PRODUCT_FILE   = 'product-file';
    const PRODUCT_ARCHIVED_FILE = 'product-archived-file';

    const SUPPLIERS_URL  = 'suppliers-url';
    const SUPPLIERS_FILE = 'suppliers-file';

    const LOGIN          = 'login';
    const PASSWORD       = 'password';
    const BASE_DIR       = 'base-dir';
    const BASE_URL       = 'base-url';

    const BASE_PRODUCTS_URL  = 'base-products-url';

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
     * @ORM\Column(name="code", type="string", length=30, unique=true)
     */
    protected $code;

    /**
     * @var string $value
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    protected $value;

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
     * @return Config
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Config
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
     * Set value
     *
     * @param string $value
     * 
     * @return Config
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
