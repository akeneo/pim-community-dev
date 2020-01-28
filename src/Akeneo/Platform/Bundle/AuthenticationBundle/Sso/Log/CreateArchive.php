<?php
declare(strict_types=1);

/*
 * this file is part of the akeneo pim enterprise edition.
 *
 * (c) 2014 akeneo sas (http://www.akeneo.com)
 *
 * for the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */
namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log;

interface CreateArchive
{
    public function create(): \SplFileInfo;
}
