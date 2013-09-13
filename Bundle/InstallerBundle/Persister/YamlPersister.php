<?php

namespace Oro\Bundle\InstallerBundle\Persister;

use Symfony\Component\Yaml\Yaml;

class YamlPersister
{
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function parse()
    {
        $data       = Yaml::parse($this->file);
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

        if (false === file_put_contents($this->file, Yaml::dump(array('parameters' => $parameters)))) {
            throw new \RuntimeException(sprintf('Failed to write to %s.', $this->file));
        }
    }
}
