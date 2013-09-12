<?php

namespace Oro\Bundle\SecurityBundle\Annotation\Loader;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;
use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationStorage;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;

class AclYamlConfigLoader extends AbstractLoader implements AclAnnotationLoaderInterface
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * Constructor
     *
     * @param KernelInterface $kernel
     * @param AclExtensionSelector $extensionSelector
     */
    public function __construct(KernelInterface $kernel, AclExtensionSelector $extensionSelector)
    {
        $this->kernel = $kernel;
        parent::__construct($extensionSelector);
    }

    /**
     * Loads ACL annotations from YAML config files
     *
     * @param AclAnnotationStorage $storage
     */
    public function load(AclAnnotationStorage $storage)
    {
        foreach ($this->kernel->getBundles() as $bundle) {
            $file = $bundle->getPath() . '/Resources/config/acl.yml';
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
