<?php

namespace Context\Page\Role;

use Context\Page\Base\Form;
use Pim\Behat\Decorator\Permission\PermissionDecorator;

/**
 * User group creation page
 *
 * @author    Jean-Marie Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    /** @var string */
    protected $path = '#/user/role/create';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            [
                'Permission' => [
                    'css'        => '#pim_user-roles-tab-action',
                    'decorators' => [
                        PermissionDecorator::class
                    ]
                ],
                'API permission' => [
                    'css'        => '#pim_user-roles-tab-api',
                    'decorators' => [
                        PermissionDecorator::class
                    ]
                ],
            ],
            $this->elements
        );
    }
}
