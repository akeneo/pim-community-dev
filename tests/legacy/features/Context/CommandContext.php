<?php

namespace Context;

use Pim\Behat\Context\PimContext;

/**
 * Context for commands
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommandContext extends PimContext
{
    /**
     * @When /^I launch the purge versions command for entity "([^"]*)"$/
     * @When /^I launch the purge versions command"$/
     *
     * @param string $entityName
     */
    public function iLaunchThePurgeCommandForEntityOlderThanDays($entityName = '')
    {
        $commandLauncher = $this->getService('pim_catalog.command_launcher');
        $commandLauncher->executeForeground(
            sprintf('pim:versioning:purge %s --more-than-days 0 --force', $entityName)
        );
    }

    /**
     * @param string $rawActions
     *
     * @return string
     */
    protected function sanitizeProductActions($rawActions)
    {
        $actions = json_decode($rawActions);

        foreach ($actions as $key => $action) {
            if (isset($action->data->filePath)) {
                $action->data->filePath = self::replacePlaceholders($action->data->filePath);
            }
        }

        return json_encode($actions);
    }

    /**
     * Recursive intersect for nested associative array
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    protected function arrayIntersect($array1, $array2)
    {
        $isAssoc = array_keys($array1) !== range(0, count($array1) - 1);
        foreach ($array1 as $key => $value) {
            if ($isAssoc) {
                if (!array_key_exists($key, $array2)) {
                    unset($array1[$key]);
                } else {
                    if (is_array($value)) {
                        $array1[$key] = $this->arrayIntersect($value, $array2[$key]);
                    }
                }
            } else {
                if (is_array($value)) {
                    $array1[$key] = $this->arrayIntersect($value, $array2[$key]);
                }
            }
        }

        return $array1;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }

    /**
     * @return FixturesContext
     */
    protected function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }

    /**
     * Runs app/console $command in the test environment
     *
     * @When /^I run '([^\']*)'( in background)?$/
     *
     * @param string      $command
     * @param string|null $command
     */
    public function iRun($command, $background)
    {
        $commandLauncher = $this->getService('pim_catalog.command_launcher');

        if (null === $background) {
            $commandLauncher->executeForeground($this->replacePlaceholders($command));
        } else {
            $commandLauncher->executeBackground($this->replacePlaceholders($command));
        }
    }
}
