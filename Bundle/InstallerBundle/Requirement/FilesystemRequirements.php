<?php

namespace Oro\Bundle\InstallerBundle\Requirement;

use Symfony\Component\Translation\TranslatorInterface;

class FilesystemRequirements extends RequirementCollection
{
    /**
     *
     * @param TranslatorInterface $translator
     * @param string              $rootDir
     * @param string              $cacheDir
     * @param string              $logsDir
     * @param string              $uploadsDir
     * @param string              $assetsDir
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(TranslatorInterface $translator, $rootDir, $cacheDir, $logsDir, $uploadsDir, $assetsDir)
    {
        parent::__construct($translator->trans('filesystem.header', array(), 'requirements'));

        $exists      = $translator->trans('filesystem.exists', array(), 'requirements');
        $notExists   = $translator->trans('filesystem.not_exists', array(), 'requirements');
        $writable    = $translator->trans('filesystem.writable', array(), 'requirements');
        $notWritable = $translator->trans('filesystem.not_writable', array(), 'requirements');

        $this
            ->add(new Requirement(
                $translator->trans('filesystem.vendors', array(), 'requirements'),
                $status = is_dir($rootDir . '/../vendor'),
                $exists,
                $status ? $exists : $notExists
            ))
            ->add(new Requirement(
                realpath($cacheDir),
                $status = is_writable($cacheDir),
                $writable,
                $status ? $writable : $notWritable,
                true,
                $translator->trans('filesystem.cache.help', array('%path%' => $cacheDir), 'requirements')
            ))
            ->add(new Requirement(
                realpath($logsDir),
                $status = is_writable($logsDir),
                $writable,
                $status ? $writable : $notWritable,
                true,
                $translator->trans('filesystem.logs.help', array('%path%' => $logsDir), 'requirements')
            ))
            ->add(new Requirement(
                realpath($uploadsDir),
                $status = is_writable($uploadsDir),
                $writable,
                $status ? $writable : $notWritable,
                true,
                $translator->trans('filesystem.uploads.help', array('%path%' => $uploadsDir), 'requirements')
            ))
            ->add(new Requirement(
                realpath($assetsDir),
                $status = is_writable($assetsDir),
                $writable,
                $status ? $writable : $notWritable,
                true,
                $translator->trans('filesystem.assets.help', array('%path%' => $assetsDir), 'requirements')
            ))
            ->add(new Requirement(
                realpath($rootDir . '/config/parameters.yml'),
                $status = is_writable($rootDir . '/config/parameters.yml'),
                $writable,
                $status ? $writable : $notWritable,
                true,
                $translator->trans('filesystem.parameters.help', array('%path%' => $rootDir . '/config/parameters.yml'), 'requirements')
            ));
    }
}
