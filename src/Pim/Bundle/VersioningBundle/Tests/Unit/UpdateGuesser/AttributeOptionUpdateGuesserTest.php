<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\UpdateGuesser;

use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\AttributeOptionUpdateGuesser;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionUpdateGuesserTest extends AbstractUpdateGuesserTest
{
    /**
     * Test related methods
     */
    public function testGuessUpdates()
    {
        $option    = new AttributeOption();
        $attribute = new Attribute();
        $attribute->addOption($option);

        $guesser   = new AttributeOptionUpdateGuesser();
        $em        = $this->getEntityManagerMock();
        $updates   = $guesser->guessUpdates($em, $option, UpdateGuesserInterface::ACTION_UPDATE_ENTITY);
        $this->assertEquals(1, count($updates));
        $this->assertEquals($attribute, $updates[0]);
    }
}
