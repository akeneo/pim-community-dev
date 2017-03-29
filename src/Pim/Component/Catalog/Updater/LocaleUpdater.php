<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Event\LocaleEvents;
use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Updates a locale
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleUpdater implements ObjectUpdaterInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * {
     *     'code': 'en_US'
     * }
     */
    public function update($locale, array $data, array $options = [])
    {
        if (!$locale instanceof LocaleInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($locale),
                LocaleInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($locale, $field, $value);
        }

        $this->eventDispatcher->dispatch(LocaleEvents::POST_UPDATE, new GenericEvent($locale));

        return $this;
    }

    /**
     * @param LocaleInterface $locale
     * @param string          $field
     * @param mixed           $data
     */
    protected function setData(LocaleInterface $locale, $field, $data)
    {
        if ('code' === $field) {
            $locale->setCode($data);
        }
    }
}
