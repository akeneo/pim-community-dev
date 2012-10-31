<?php
namespace Akeneo\CatalogBundle\Entity;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityField as AbstractEntityField;
use Doctrine\ORM\Mapping as ORM;

/**
 * Product field as sku, name, etc
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="AkeneoCatalog_Product_Field")
 * @ORM\Entity
 */
class ProductField extends AbstractEntityField
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
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    protected $code;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    protected $type;

    /**
     * @ORM\Column(name="uniqueValue", type="boolean")
     */
    protected $uniqueValue;

    /**
     * @ORM\Column(name="valueRequired", type="boolean")
     */
    protected $valueRequired;

    /**
     * @ORM\Column(name="searchable", type="boolean")
     */
    protected $searchable;

}