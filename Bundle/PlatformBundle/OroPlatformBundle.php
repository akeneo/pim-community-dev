<?php

namespace Oro\Bundle\PlatformBundle;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroPlatformBundle extends Bundle
{
    public static function registeredBundles(Kernel $kernel)
    {
        return array(
            new \Oro\Bundle\FlexibleEntityBundle\OroFlexibleEntityBundle(),
            new \Oro\Bundle\UIBundle\OroUIBundle(),
            new \Oro\Bundle\FormBundle\OroFormBundle(),
            new \Oro\Bundle\JsFormValidationBundle\OroJsFormValidationBundle(),
            new \Oro\Bundle\SoapBundle\OroSoapBundle(),
            new \Oro\Bundle\SearchBundle\OroSearchBundle(),
            new \Oro\Bundle\UserBundle\OroUserBundle(),
            new \Oro\Bundle\MeasureBundle\OroMeasureBundle(),
            new \Oro\Bundle\SegmentationTreeBundle\OroSegmentationTreeBundle(),
            new \Oro\Bundle\NavigationBundle\OroNavigationBundle(),
            new \Oro\Bundle\ConfigBundle\OroConfigBundle(),
            new \Oro\Bundle\FilterBundle\OroFilterBundle(),
            new \Oro\Bundle\GridBundle\OroGridBundle(),
            new \Oro\Bundle\WindowsBundle\OroWindowsBundle(),
            new \Oro\Bundle\AddressBundle\OroAddressBundle(),
            new \Oro\Bundle\DataAuditBundle\OroDataAuditBundle(),
            new \Oro\Bundle\TagBundle\OroTagBundle(),
            new \Oro\Bundle\AsseticBundle\OroAsseticBundle(),
            new \Oro\Bundle\OrganizationBundle\OroOrganizationBundle(),
            new \Oro\Bundle\NotificationBundle\OroNotificationBundle($kernel),
            new \Oro\Bundle\TranslationBundle\OroTranslationBundle(),
            new \Oro\Bundle\EmailBundle\OroEmailBundle(),
            new \Oro\Bundle\EntityBundle\OroEntityBundle(),
            new \Oro\Bundle\EntityConfigBundle\OroEntityConfigBundle(),
            new \Oro\Bundle\EntityExtendBundle\OroEntityExtendBundle(),
            new \Oro\Bundle\CronBundle\OroCronBundle(),
            new \Oro\Bundle\WorkflowBundle\OroWorkflowBundle(),
            new \Oro\Bundle\SyncBundle\OroSyncBundle(),
            new \Oro\Bundle\PlatformBundle\OroPlatformBundle(),
            new \Oro\Bundle\InstallerBundle\OroInstallerBundle(),
        );
    }
}
