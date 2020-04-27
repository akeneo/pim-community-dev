<?php
declare(strict_types=1);


namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

final class RoutesDictionary
{
    const API_PRODUCT = [
        'pim_api.controller.product:listAction',
        'pim_api.controller.product:getAction',
        'pim_api.controller.product:createAction',
        'pim_api.controller.product:partialUpdateAction',
        'pim_api.controller.product:partialUpdateListAction',
        'pim_api.controller.product:deleteAction'
    ];
}
