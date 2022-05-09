<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Context\Page\Connections;

use Akeneo\Connectivity\Connection\Tests\EndToEnd\Decorator\Settings\EditForm;
use Context\Page\Base\Base;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Base
{
    /** @var string */
    protected $path = '#/connect/connection-settings/';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Data source connections list' => ['css' => '[data-testid="data_source"]'],
                'Data destination connections list' => ['css' => '[data-testid="data_destination"]'],
                'Other connections list' => ['css' => '[data-testid="data_other"]'],
                'Edit form' => [
                    'css'        => '.AknConnectivityConnection-view',
                    'decorators' => [EditForm::class]
                ],
            ]
        );
    }
}
