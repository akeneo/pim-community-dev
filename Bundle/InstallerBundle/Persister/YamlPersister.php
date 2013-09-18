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
     * @param string $paramFile Path to parameters.yml file
     */
    public function __construct($paramFile)
    {
        $this->paramFile = $paramFile;
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
            throw new \RuntimeException(sprintf('Failed to write to %s.', $this->file));
        }
    }
}
