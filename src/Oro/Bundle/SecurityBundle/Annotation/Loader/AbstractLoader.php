<?php

namespace Oro\Bundle\SecurityBundle\Annotation\Loader;

abstract class AbstractLoader
{
    /**
     * @var string[]
     */
    protected $bundleDirectories;

    /**
     * Constructor
     *
     * @param string[] $bundles A list of loaded bundles
     */
    public function __construct($bundles)
    {
        $this->bundleDirectories = [];
        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $this->bundleDirectories[] = dirname($reflection->getFilename());
        }
    }

    /**
     * @param array $bundleDirectories
     */
    public function setBundleDirectories(array $bundleDirectories)
    {
        $this->bundleDirectories = $bundleDirectories;
    }
}
