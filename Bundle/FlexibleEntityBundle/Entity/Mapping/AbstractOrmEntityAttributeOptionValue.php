<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Oro\Bundle\FlexibleEntityBundle\Model\Entity\AbstractEntityAttributeOptionValue;
use Oro\Bundle\FlexibleEntityBundle\Model\Entity\AbstractEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base Doctrine ORM entity attribute option value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 *
 */
abstract class AbstractOrmEntityAttributeOptionValue extends AbstractEntityAttributeOptionValue
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
     * @var AbstractOrmEntityAttributeOption $option
     *
     * @ORM\ManyToOne(targetEntity="AbstractOrmEntityAttributeOption", inversedBy="optionValues")
     */
    protected $option;

    /**
     * Locale scope
     * @var string $localeCode
     *
     * @ORM\Column(name="locale", type="string", length=5, nullable=false)
     */
    protected $localeCode;

    /**
     * @var string $value
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    protected $value;

}
