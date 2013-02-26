<?php
namespace Pim\Bundle\ConfigBundle\Tests\Entity;

use Pim\Bundle\ConfigBundle\Entity\Language;

/**
 * Language Test entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LanguageTestEntity
{

    /**
     * @var \Pim\Bundle\ConfigBundle\Entity\Language
     */
    protected $entity;

    /**
     * Constructor
     *
     * @param array $datas
     */
    public function __construct(array $datas = array())
    {
        $this->entity = new Language();
        $this->fromArray($datas);
    }

    /**
     * Set properties from array
     *
     * @param array $datas
     *
     * @return \Pim\Bundle\ConfigBundle\Tests\Entity\LanguageEntityTest
     */
    public function fromArray($datas)
    {
        foreach ($datas as $key => $value) {
            $method = 'set'. ucfirst($key);
            if (method_exists(get_class($this->entity), $method)) {
                $this->entity->$method($value);
            }
        }

        return $this;
    }

    /**
     * Get tested entity
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Language
     */
    public function getTestedEntity()
    {
        return $this->entity;
    }
}
