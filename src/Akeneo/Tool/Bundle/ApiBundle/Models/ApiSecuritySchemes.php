<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Models;

use OpenApi\Attributes as OA;

#[OA\SecurityScheme(
    securityScheme: 'bearerToken',
    type: 'http',
    scheme: 'bearer',
)]
#[OA\SecurityScheme(
    securityScheme: 'basicToken',
    type: 'http',
    scheme: 'basic',
)]
class ApiSecuritySchemes
{

}