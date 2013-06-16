<?php

namespace Pim\Bundle\TranslationBundle\Tests\Entity;

use Pim\Bundle\TranslationBundle\Entity\AbstractTranslatableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Test class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Gedmo\TranslationEntity(class="Pim\Bundle\TranslationBundle\Tests\Entity\ItemTranslation")
 */
class Item extends AbstractTranslatableEntity implements Translatable
{

    /**
     * @var string $name
     */
    protected $name;

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
     * Set name
     *
     * @param string $name
     *
     * @return Item
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
