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

namespace Context\Page\FranklinInsights\Mapping\Attributes;

use Behat\Mink\Element\NodeElement;
use Context\Page\Base\Form;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class Index extends Form
{
    /** @var string */
    protected $path = '#/franklin-insights/mapping/attributes/index';

    /**
     * @param string $targetAttribute
     * @param string $value
     *
     * @throws \Context\Spin\TimeoutException
     */
    public function fillAttributeMappingField(string $targetAttribute, string $value): void
    {
        $select2 = $this->getMappingFieldForFranklinAttribute($targetAttribute);

        if ('' === $value) {
            if ($select2->hasClass('select2-allowclear')) {
                $select2->mouseOver();
                $this->spin(
                    function () use ($select2) {
                        $closeElement = $select2->find('css', '.select2-search-choice-close');
                        if (null !== $closeElement && $closeElement->isVisible()) {
                            $closeElement->click();
                            $values = array_filter(array_map('trim', $select2->getValues()));

                            return $select2->isValid() && empty($values);
                        };

                        return false;
                    },
                    'Could not remove value from select'
                );
            }
        } else {
            $select2->setValue($value);
        }
    }

    /**
     * @param string $targetAttribute
     *
     * @return ElementDecorator
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     * @throws \Context\Spin\TimeoutException
     */
    private function getMappingFieldForFranklinAttribute(string $targetAttribute): ElementDecorator
    {
        $field = $this->spin(
            function () use ($targetAttribute): ?NodeElement {
                $select2 = $this->find(
                    'css',
                    '.attribute-selector[data-franklin-attribute-code="' . $targetAttribute . '"] .select2-container'
                );
                if (null === $select2) {
                    return null;
                }

                return $select2;
            },
            'Mapping field ' . $targetAttribute . ' was not found'
        );

        $container = $this->getClosest($field, 'AknFieldContainer');
        $select2Container = $this->spin(
            function () use ($container) {
                return $container->find('css', '.select2-container');
            },
            'Can not find the select2 container.'
        );

        return $this->decorate(
            $select2Container,
            [Select2Decorator::class]
        );
    }
}
