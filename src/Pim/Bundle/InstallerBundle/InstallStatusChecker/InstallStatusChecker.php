<?php

declare(strict_types=1);

namespace Pim\Bundle\InstallerBundle\InstallStatusChecker;

use Symfony\Component\Yaml\Yaml;

/**
 * InstallStatusChecker manager : manage the very important file and flag that allow us to know
 * if the PIM has been installed and when.
 * As the location of the file containing the flag is configurable (install_status_dir), the flag could not be load
 * directly in the container.
 * The parameter "install_status_dir" can be a relative path to the project dir or an absolute path.
 *
 *
 * @author    Vincent Berruchon <vincent.berruchon@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallStatusChecker
{
    const INSTALL_STATUS_FILENAME_PREFIX = 'install';
    const INSTALL_STATUS_FILENAME_EXT = '.yml';
    const ATTR_INSTALL_STATUS_DIR = 'install_status_dir';
    const ATTR_INSTALLED_STATUS_SECTION ='install';
    const ATTR_INSTALLED_STATUS ='installed';

    /**
     * Path to the project directory
     * @var string $projectDir
     */
    protected $projectDir;

    /**
     * Absolute or relative path to the directory to the install flag file.
     * @var string $absoluteDirectory
     */
    protected $absoluteDirectoryPath;

    /**
     * Path to install status file
     *
     * @var string $absoluteInstallStatusFilePath
     */
    protected $absoluteFilePath;

    /**
     * environment
     * @var string $env
     */
    protected $env;

    /**
     * @param string $aProjectDir Path to parameters storage directory
     * @param string string $aStatusFileDir
     * @param string $aEnv
     */
    public function __construct(string $aProjectDir, string $aStatusFileDir, string $aEnv)
    {
        if (empty($aStatusFileDir)) {
            throw new \RuntimeException('Please configure ' . self::ATTR_INSTALL_STATUS_DIR);
        }
        $this->projectDir = $aProjectDir;
        $this->env = $aEnv;

        if (mb_substr($aStatusFileDir, 0, 1) === '/') {
            $this->absoluteDirectoryPath = $aStatusFileDir;
        } else {
            $this->absoluteDirectoryPath = $this->projectDir . '/' . $aStatusFileDir;
        }
        $this->absoluteFilePath = $this->absoluteDirectoryPath. '/' . self::INSTALL_STATUS_FILENAME_PREFIX
            . $this->getEnvSuffixe($this->env)
            . self::INSTALL_STATUS_FILENAME_EXT;
    }

    public function getAbsoluteDirectoryPath() : string
    {
        return $this->absoluteDirectoryPath;
    }

    public function getAbsoluteFilePath() : string
    {
        return $this->absoluteFilePath;
    }

    /**
     * @return string return the string 'false' if not installed or return the timestamp of installation.
     */
    public function getInstalledFlag() : string
    {
        // if PIM not installed, the file probably doesn't exist
        if (!file_exists($this->absoluteFilePath)) {
            return 'false';
        }
        $data = Yaml::parse(file_get_contents($this->absoluteFilePath));
        if (!is_array($data) || !isset($data[self::ATTR_INSTALLED_STATUS_SECTION])
         || !is_array($data[self::ATTR_INSTALLED_STATUS_SECTION])) {
            return 'false';
        }
        if (array_key_exists(self::ATTR_INSTALLED_STATUS, $data[self::ATTR_INSTALLED_STATUS_SECTION])) {
            $installed = $data[self::ATTR_INSTALLED_STATUS_SECTION][self::ATTR_INSTALLED_STATUS];
            if (empty($installed)||$installed==='false') {
                return 'false';
            } else {
                return $installed;
            }
        }
        return 'false';
    }

    /**
    * @return bool Return a boolean about installation state of the PIM
    */
    public function isInstalled() : bool
    {
        if ($this->getInstalledFlag()==='false') {
            return false;
        }
        return true;
    }

    /**
    * @param string $aInstallTime
    */
    public function setInstallStatus(string $aInstallTime) : void
    {
        $install = [];
        $install[self::ATTR_INSTALLED_STATUS] = $aInstallTime;

        if (false === file_put_contents(
            $this->absoluteFilePath,
            Yaml::dump([self::ATTR_INSTALLED_STATUS_SECTION => $install])
        )) {
            throw new \RuntimeException(
                sprintf(
                    'Failed to write to "%s". "%s" status not set!',
                    $this->absoluteFilePath,
                    self::ATTR_INSTALLED_STATUS
                )
            );
        }
    }

    /**
     * @param string $aEnv
     * @return string If behat or test we add "_test" (will be finally "install_test.yml").
     *  For prod and test, add nothing.
     */
    protected function getEnvSuffixe(string $aEnv) : string
    {
        if ($aEnv==="behat"||$aEnv==="test") {
            return '_test';
        }
        return '';
    }
}
