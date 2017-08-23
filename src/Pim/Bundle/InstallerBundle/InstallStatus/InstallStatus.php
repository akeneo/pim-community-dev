<?php

namespace Pim\Bundle\InstallerBundle\InstallStatus;

use Symfony\Component\Yaml\Yaml;

/**
 * InstallStatus manager : manage the very important file and flag that allow us to know
 * if the PIM has been installed and when.
 *
 * @author    Vincent Berruchon <vincent.berruchon@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallStatus
{
    const INSTALL_STATUS_FILENAME = 'install_status.yml';

    /**
     * Path to install status file
     *
     * @var string
     */
    protected $installStatusFile;

    /**
     * @param string $dir Path to parameters storage directory
     */
    public function __construct($dir)
    {
        if (!empty($dir) ) {
            $this->installStatusFile = $dir . '/' . self::INSTALL_STATUS_FILENAME ;
        } else {
            throw new \RuntimeException(sprintf('Please configure install_status_dir!'));
        }
    }

    /**
     * @return array
     */
    public function parse()
    {
        if ( !file_exists($this->installStatusFile) ) // if PIM not installed, the file probably doesn't exist
        {
            return [];
        }

        $data = Yaml::parse(file_get_contents($this->installStatusFile));
        if (!is_array($data) || !isset($data['install'])) {
            return [];
        }

        $parameters = [];

        foreach ($data['install'] as $key => $value) {
            $parameters[$key] = $value;
        }

        return $parameters;
    }

    /**
     * @param array $data
     */
    public function dump(array $data)
    {
        $parameters = [];

        foreach ($data as $section) {
            foreach ($section as $key => $value) {
                $parameters[$key] = $value;
            }
        }
        if (false === file_put_contents($this->installStatusFile, Yaml::dump(['install' => $parameters]))) {
            throw new \RuntimeException(sprintf('Failed to write to %s.', $this->installStatusFile));
        }
    }

    /**
     * @return string return the string 'false' if not installed or return the timestamp of installation.
     */
     public function getInstalledFlag()
     {
         if ( !file_exists($this->installStatusFile) ) // if PIM not installed, the file probably doesn't exist
         {
             return 'false';
         }
         $data = Yaml::parse(file_get_contents($this->installStatusFile));
         if (!is_array($data) || !isset($data['install']) || !is_array($data['install']) ) {
             return 'false';
         }
         if (array_key_exists('installed', $data['install'])) {
             $installed = $data['install']['installed'];
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
     public function isInstalled()
     {
         $installed = $this->getInstalledFlag();
         if ( $installed==='false' )
         {
             return false;
         } else {
             return true;
         }
     }

     /**
      * @param $installTime
      */
     public function  setInstallStatus( $installTime )
     {
         $install = [];
         $install['installed'] = $installTime;

         if (false === file_put_contents($this->installStatusFile, Yaml::dump(['install' => $install]))) {
             throw new \RuntimeException(sprintf('Failed to write to %s.', $this->installStatusFile));
         }

     }

}
