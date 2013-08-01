<?php

namespace Context;

use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Exception\ExpectationException;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Main feature context
 *
 * @author    Gildas Quéméner <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
    private $kernel;

    public function __construct(array $parameters)
    {
        $this->useContext('fixtures', new FixturesContext());
        $this->useContext('webUser', new WebUser());
        $this->useContext('webApi', new WebApiContext($parameters['base_url']));
        $this->useContext('datagrid', new DataGridContext());
    }

    /**
     * @BeforeScenario
     */
    public function purgeDatabase()
    {
        $purger = new ORMPurger($this->getEntityManager());
        $purger->purge();
    }

    /**
     * @AfterScenario
     */
    public function closeConnection()
    {
        $this->getEntityManager()->getConnection()->close();
    }

    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel HttpKernel instance
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns Container instance.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->kernel->getContainer();
    }

    public function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    public function listToArray($list)
    {
        return explode(', ', str_replace(' and ', ', ', $list));
    }

    public function createExpectationException($message)
    {
        return new ExpectationException($message, $this->getSession());
    }

    public function wait($time = 5000, $condition = 'document.readyState == "complete" && !$.active')
    {
        $this->getSession()->wait($time, $condition);
    }
}
