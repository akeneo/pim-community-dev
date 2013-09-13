<?php

namespace Oro\Bundle\SecurityBundle\Annotation\Loader;

use Symfony\Component\Yaml\Yaml;
use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationStorage;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

class AclYamlConfigLoader extends AbstractLoader implements AclAnnotationLoaderInterface
{
    /**
     * Constructor
     *
     * @param ServiceLink $extensionSelectorLink
     */
    public function __construct(ServiceLink $extensionSelectorLink)
    {
        parent::__construct($extensionSelectorLink);
    }

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
                $config = Yaml::parse(realpath($file));
                foreach ($config as $id => $data) {
                    $data['id'] = $id;
                    $annotation = new AclAnnotation($data);
                    $this->postLoadAnnotation($annotation);
                    $storage->add($annotation);
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
