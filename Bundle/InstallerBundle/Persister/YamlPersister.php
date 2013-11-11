<?php

namespace Oro\Bundle\InstallerBundle\Persister;

use Symfony\Component\Yaml\Yaml;

class YamlPersister
{
    /**
     * Path to parameters.yml file
     *
     * @var string
     */
    protected $paramFile;

    /**
     * @param string $dir Path to parameters storage directory
     * @param string $env Current environment
     */
    public function __construct($dir, $env)
    {
        if (file_exists($file = $dir . '/parameters_' . $env . '.yml')) {
            $this->paramFile = $file;
        } elseif (file_exists($dir . '/parameters_' . $env . '.yml.dist')) {
            $this->paramFile = $dir . '/parameters_' . $env . '.yml';
        } else {
            $this->paramFile = $dir . '/parameters.yml';
        }
    }

    public function parse()
    {
        $data = Yaml::parse($this->paramFile);

        if (!is_array($data) || !isset($data['parameters'])) {
            return array();
        }

        $parameters = array();

        foreach ($data['parameters'] as $key => $value) {
            $section = explode('_', $key);
            $section = isset($section[1]) ? $section[0] : 'system';

            if (!isset($parameters[$section])) {
                $parameters[$section] = array();
            }

            $parameters[$section]['oro_installer_' . $key] = $value;
        }

        return $parameters;
    }

    public function dump(array $data)
    {
        $parameters = array();

        foreach ($data as $section) {
            foreach ($section as $key => $value) {
                $parameters[str_replace('oro_installer_', '', $key)] = $value;
            }
        }

        if (false === file_put_contents($this->paramFile, Yaml::dump(array('parameters' => $parameters)))) {
            throw new \RuntimeException(sprintf('Failed to write to %s.', $this->paramFile));
        }
    }
}
