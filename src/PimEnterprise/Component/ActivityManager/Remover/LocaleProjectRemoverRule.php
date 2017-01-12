<?php

namespace PimEnterprise\Component\ActivityManager\Remover;

use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class LocaleProjectRemoverRule implements ProjectRemoverRuleInterface
{
    /**
     * A project has to be removed if its locale is now deactivated or if its locale is no longer part
     * of its channel locales.
     *
     * {@inheritdoc}
     */
    public function hasToBeRemoved(ProjectInterface $project, $locale)
    {
        if (!$locale instanceof LocaleInterface || $project->getLocale()->getCode() !== $locale->getCode()) {
            return false;
        }
        if (!$locale->isActivated()) {
            return true;
        }

        $localesCodeChannel = $project->getChannel()->getLocaleCodes();
        $projectLocaleCode = $project->getLocale()->getCode();
        if (!in_array($projectLocaleCode, $localesCodeChannel)) {
            return true;
        }

        return false;
    }
}
