<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

/**
 * Query the attributes mapping of a family.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingByFamilyQuery
{
    /** @var string */
    private $familyCode;

    /**
     * @param string $familyCode
     */
    public function __construct(string $familyCode)
    {
        if (empty($familyCode)) {
            throw new \InvalidArgumentException('Family code should not be empty');
        }

        $this->familyCode = $familyCode;
    }

    /**
     * @return string
     */
    public function getFamilyCode(): string
    {
        return $this->familyCode;
    }
}
