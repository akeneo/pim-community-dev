<?php

namespace Context\Page\Asset;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Context\Page\Base\Grid;

/**
 * Product assets index page
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Grid
{
    /** @var string */
    protected $path = '/enrich/asset/';

    /**
     * @return NodeElement|mixed|null
     */
    public function getDialog()
    {
        return $this->find('css', '.ui-dialog');
    }


    /**
     * @return NodeElement|mixed|null
     */
    public function getLocalizableSwitch()
    {
        return $this->getDialog()->find('css', '.has-switch');
    }

    /**
     * @param string $state Must be 'on' or 'off'
     */
    public function changeLocalizableSwitch($state)
    {
        $switch = $this->getLocalizableSwitch();
        $animationBlock = $switch->find('css', '.switch-animate');
        if (!$animationBlock->hasClass(sprintf('switch-%s', $state))) {
            $animationBlock->find('css', 'label.switch-small')->click();
        }
        $referenceField = $this->find('css', '.reference-field');

        if ('on' === $state) {
            $this->spin(function () use ($referenceField) {
                return !$referenceField->isVisible();
            });
        } else {
            $this->spin(function () use ($referenceField) {
                return $referenceField->isVisible();
            });
        }
    }

    /**
     * @throws ElementNotFoundException
     *
     * @return Element
     */
    public function findReferenceUploadZone()
    {
        $uploadZone = $this->getDialog()->find('css', '.reference-field .asset-uploader');

        if (!$uploadZone) {
            throw new ElementNotFoundException($this->getSession(), 'reference upload zone');
        }

        return $uploadZone;
    }
}
