<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Context\Page\FranklinInsights\Mapping\Identifiers;

use Behat\Mink\Element\NodeElement;
use Context\Page\Base\Form;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class Index extends Form
{
    /** @var string */
    protected $path = '#/franklin-insights/mapping/identifiers/edit';

    /**
     * @param string $identifier
     * @param string $value
     *
     * @throws \Context\Spin\TimeoutException
     */
    public function fillIdentifierMappingField(string $identifier, string $value): void
    {
        $select2 = $this->spin(function () use ($identifier): ?NodeElement {
            $select2 = $this->find('css', '.AknFormContainer[data-identifier="'.$identifier.'"] .select2-container');
            if (null === $select2) {
                return null;
            }
            return $select2;
        }, 'Mapping field '.$identifier.' was not found');

        $this->fillSelect2Field($select2, $value);
    }
}
