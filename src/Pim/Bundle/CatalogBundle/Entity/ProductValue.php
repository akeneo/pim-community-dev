<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Bap\Bundle\FlexibleEntityBundle\Model\Entity as AbstractEntity;
use Bap\Bundle\FlexibleEntityBundle\Model\EntityValue as AbstractEntityValue;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * Value for a product field
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="Akeneo_PimCatalog_Product_Value")
 * @ORM\Entity
 * @Gedmo\TranslationEntity(class="Pim\Bundle\CatalogBundle\Entity\ProductTranslation")
 */
class ProductValue extends AbstractEntityValue
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
     * @var Field $field
     *
     * @ORM\ManyToOne(targetEntity="ProductField")
     */
    protected $field;

    /**
     * @var Entity $entity
     *
     * @ORM\ManyToOne(targetEntity="ProductEntity", inversedBy="values")
     */
    protected $entity;

    /**
     * TODO : basic sample for basic EAV implementation, only varchar values
     * @var string $content
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="data", type="string", length=255)
     */
    protected $data;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    protected $locale;

    /**
     * Set used locale
     * @param string $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Set entity
     *
     * @param AbstractEntity $entity
     * @return ProductValue
     */
    public function setEntity(AbstractEntity $entity = null)
    {
    $this->entity = $entity;

    return $this;
    }
}