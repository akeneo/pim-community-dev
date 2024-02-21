<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Context\Page\Connections;

use Context\Page\Base\Base;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Create extends Base
{
    /** @var string */
    protected $path = '#/connections/create';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Creation form' => [
                    'css'        => '[data-testid="create-connection"]',
                    'decorators' => ['Akeneo\Connectivity\Connection\Tests\EndToEnd\Decorator\Settings\CreationForm']
                ],
            ]
        );
    }
}
