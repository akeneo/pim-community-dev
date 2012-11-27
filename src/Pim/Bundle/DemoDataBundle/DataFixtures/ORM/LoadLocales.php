<?php
namespace Pim\Bundle\CatalogBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogTaxinomyBundle\Entity\Locale;

/**
 * Load locales
 *
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadLocales extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // add locales
        $locale = new Locale();
        $locale->setCode('en_US');
        $locale->setIsDefault(true);
        $manager->persist($locale);

        $locale = new Locale();
        $locale->setCode('fr_FR');
        $locale->setIsDefault(false);
        $manager->persist($locale);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

}
