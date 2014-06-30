<?php

namespace Context;

use Context\WebUser as BaseWebUser;

/**
 * Overrided context
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseWebUser extends BaseWebUser
{
    /**
     * Override parent
     */
    public function iChooseTheOperation($operation)
    {
        $page = $this->getNavigationContext()->currentPage = $this
            ->getPage('Batch Operation');
        $page->addStep('Publish products', 'Batch Publish');

        $page->chooseOperation($operation)
            ->next();

        $this->wait();
    }
}
