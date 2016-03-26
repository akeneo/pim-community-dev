<?php
namespace Pim\Bundle\InstallerBundle\Event;

class InstallEvents
{
    /**
     * InstallCommand events
     */
    const PRE_INSTALL   = "pim_install.pre_install";
    const POST_INSTALL  = "pim_install.post_install";


    /**
     * AssetCommand events
     */
    const ASSETS_PRE_INSTALL        = "pim_install.assets.pre_install";
    const ASSETS_POST_INSTALL       = "pim_install.assets.post_install";
    const ASSETS_DUMP               = "pim_install.assets.dump";
    const ASSETS_DUMP_TRANSLATIONS  = "pim_install.assets.dump_translations";
}