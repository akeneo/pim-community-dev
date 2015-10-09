<?php

namespace Oro\Bundle\SecurityBundle\Annotation\Loader;

use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationStorage;
use Symfony\Component\Yaml\Yaml;

class AclYamlConfigLoader extends AbstractLoader implements AclAnnotationLoaderInterface
{
    /**
     * Loads ACL annotations from YAML config files
     *
     * @param AclAnnotationStorage $storage
     */
    public function load(AclAnnotationStorage $storage)
    {
        foreach ($this->bundleDirectories as $bundleDir) {
            $file = $bundleDir . '/Resources/config/acl.yml';
            if (is_file($file)) {
                $config = Yaml::parse(file_get_contents(realpath($file)));
                foreach ($config as $id => $data) {
                    $data['id'] = $id;
                    $storage->add(new AclAnnotation($data));
                    if (isset($data['bindings'])) {
                        foreach ($data['bindings'] as $binding) {
                            $storage->addBinding(
                                $id,
                                isset($binding['class']) ? $binding['class'] : null,
                                isset($binding['method']) ? $binding['method'] : null
                            );
                        }
                    }
                }
            }
        }
    }
}
