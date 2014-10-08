<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\UpdateGuesser;

use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\CategoryTranslation;
use Pim\Bundle\VersioningBundle\UpdateGuesser\TranslationsUpdateGuesser;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationsUpdateGuesserTest extends AbstractUpdateGuesserTest
{
    /**
     * Test related methods
     */
    public function testGuessUpdates()
    {
        $category    = new Category();
        $translation = new CategoryTranslation();
        $translation->setLocale('en_US');
        $translation->setForeignKey($category);

        $guesser   = new TranslationsUpdateGuesser(array('Pim\Bundle\CatalogBundle\Entity\Category'));
        $em        = $this->getEntityManagerMock();
        $updates   = $guesser->guessUpdates($em, $translation, UpdateGuesserInterface::ACTION_UPDATE_ENTITY);
        $this->assertEquals(1, count($updates));
        $this->assertEquals($category, $updates[0]);
    }
}
