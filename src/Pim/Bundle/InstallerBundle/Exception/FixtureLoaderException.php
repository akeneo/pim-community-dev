<?php

namespace Pim\Bundle\InstallerBundle\Exception;

use Akeneo\Component\Batch\Item\InvalidItemException;
use Symfony\Component\Yaml\Yaml;

/**
 * Fixture loader exception
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixtureLoaderException extends \RuntimeException
{
    /**
     * Constructor
     *
     * @param array                $fixtureConfig
     * @param \Akeneo\Component\Batch\Item\InvalidItemException $previous
     */
    public function __construct(array $fixtureConfig, InvalidItemException $previous)
    {
        $message = sprintf(
            "%s\n%s",
            $previous->getMessage(),
            Yaml::dump(
                [
                    'fixture' => $fixtureConfig,
                    'item'    => $previous->getItem()
                ],
                2
            )
        );
        parent::__construct($message);
    }
}
