<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\UpdateGuesser;

use Pim\Bundle\VersioningBundle\UpdateGuesser\TranslationsUpdateGuesser;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\CategoryTranslation;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationsUpdateGuesserTest extends \PHPUnit_Framework_TestCase
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

        $guesser   = new TranslationsUpdateGuesser();
        $em        = $this->getEntityManagerMock();
        $updates   = $guesser->guessUpdates($em, $translation);
        $this->assertEquals(1, count($updates));
        $this->assertEquals($category, $updates[0]);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManagerMock()
    {
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}
