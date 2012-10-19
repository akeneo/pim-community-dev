<?php
namespace Bap\Bundle\FlexibleEntityBundle\Model;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Abstract entity type, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class EntityType
{

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $code
     */
    protected $code;

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
     * @return ProductType
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

}