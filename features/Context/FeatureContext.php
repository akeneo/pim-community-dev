<?php

namespace Context;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;

/**
 * Main feature context
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
    private $kernel;

    /**
     * Path of the yaml file containing tables that should be excluded from database purge
     * @var string
     */
    private $excludedTablesFile = 'excluded_tables.yml';

    /**
     * Register contexts
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->useContext('fixtures', new FixturesContext());
        $this->useContext('webUser', new WebUser($parameters['window_width'], $parameters['window_height']));
        $this->useContext('webApi', new WebApiContext($parameters['base_url']));
        $this->useContext('datagrid', new DataGridContext());
        $this->useContext('command', new CommandContext());
        $this->useContext('navigation', new NavigationContext());
        $this->useContext('transformations', new TransformationContext());
    }

    /**
     * @BeforeScenario
     */
    public function purgeDatabase()
    {
        $excludedTablesFile = __DIR__ . '/' . $this->excludedTablesFile;
        if (file_exists($excludedTablesFile)) {
            $parser = new Parser();
            $excludedTables = $parser->parse(file_get_contents($excludedTablesFile));
            $excludedTables = $excludedTables['excluded_tables'];
        } else {
            $excludedTables = array();
        }
        $purger = new SelectiveORMPurger($this->getEntityManager(), $excludedTables);
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

    /**
     * Return doctrine manager instance
     *
     * @return ObjectManager
     */
    public function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Transform a list to array
     *
     * @param string $list
     *
     * @return array
     */
    public function listToArray($list)
    {
        return explode(', ', str_replace(' and ', ', ', $list));
    }

    /**
     * Create an expectation exception
     *
     * @param string $message
     *
     * @return ExpectationException
     */
    public function createExpectationException($message)
    {
        return new ExpectationException($message, $this->getSession());
    }

    /**
     * Wait
     *
     * @param integer $time
     * @param string  $condition
     */
    public function wait($time = 5000, $condition = null)
    {
        $condition = $condition !== null ? $condition : <<<JS
        document.readyState == 'complete'                  // Page is ready
            && typeof $ != 'undefined'                     // jQuery is loaded
            && !$.active                                   // No ajax request is active
            && $('#page').css('display') == 'block'        // Page is displayed (no progress bar)
            && $('.loading-mask').css('display') == 'none' // Page is not loading (no black mask loading page)
            && $('.jstree-loading').length == 0;           // Jstree has finished loading
JS;

        try {
            $this->getSession()->wait(100, false);
            $this->getSession()->wait($time, $condition);
        } catch (UnsupportedDriverActionException $e) {
        }
    }

    /**
     * Get the mail recorder
     *
     * @return MailRecorder
     */
    public function getMailRecorder()
    {
        return $this->getContainer()->get('pim_catalog.mailer.mail_recorder');
    }
}
