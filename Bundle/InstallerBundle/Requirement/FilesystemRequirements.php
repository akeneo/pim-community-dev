<?php

namespace Oro\Bundle\InstallerBundle\Requirement;

use Symfony\Component\Translation\TranslatorInterface;

class FilesystemRequirements extends RequirementCollection
{
    public function __construct(TranslatorInterface $translator, $rootDir, $cacheDir, $logsDir)
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
                $translator->trans('filesystem.cache.header', array(), 'requirements'),
                $status = is_writable($cacheDir),
                $translator->trans('filesystem.writable', array(), 'requirements'),
                $status ? $translator->trans('filesystem.writable', array(), 'requirements') : $translator->trans('filesystem.not_writable', array(), 'requirements'),
                true,
                $translator->trans('filesystem.cache.help', array('%path%' => $cacheDir), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('filesystem.logs.header', array(), 'requirements'),
                $status = is_writable($logsDir),
                $writable,
                $status ? $writable : $notWritable,
                true,
                $translator->trans('filesystem.logs.help', array('%path%' => $logsDir), 'requirements')
            ))
            ->add(new Requirement(
                $translator->trans('filesystem.parameters.header', array(), 'requirements'),
                $status = is_writable($rootDir . '/config/parameters.yml'),
                $writable,
                $status ? $writable : $notWritable,
                true,
                $translator->trans('filesystem.parameters.help', array('%path%' => $rootDir . '/config/parameters.yml'), 'requirements')
            ))
        ;
    }
}
