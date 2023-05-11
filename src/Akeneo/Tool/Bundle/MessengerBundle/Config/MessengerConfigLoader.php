<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Config;

use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessengerConfigLoader
{
    private const CONFIG_FILEPATH = 'config/messages.yml';
    private const CONFIG_FILEPATH_FOR_ENV = 'config/messages_%s.yml';

    public static function loadConfig(string $projectDir, string $env): array
    {
        $config = [];
        $configFile = $projectDir . '/' . self::CONFIG_FILEPATH;
        if (\file_exists($configFile)) {
            Assert::fileExists($configFile);
            $config = Yaml::parse(file_get_contents($configFile));
        }

        $configFileForEnv = $projectDir . '/' . \sprintf(self::CONFIG_FILEPATH_FOR_ENV, $env);
        if (\file_exists($configFileForEnv)) {
            $testConfig = Yaml::parse(file_get_contents($configFileForEnv));
            $config['queues'] = \array_merge($config['queues'] ?? [], $testConfig['queues']);
        }

        return $config;
    }
}
