<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Locale;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

final class InMemoryLocaleRepository implements LocaleRepositoryInterface, SaverInterface
{
    /** @var Collection */
    private $locales;

    public function __construct()
    {
        $this->locales = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->locales->get($code);
    }

    /**
     * {@inheritdoc}
     */
    public function save($locale, array $options = [])
    {
        $this->locales->set($locale->getCode(), $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $locales = [];
        foreach ($this->locales as $locale) {
            $keepThisLocale = true;
            foreach ($criteria as $key => $value) {
                $getter = sprintf('get%s', ucfirst($key));
                if ($locale->$getter() !== $value) {
                    $keepThisLocale = false;
                }
            }

            if ($keepThisLocale) {
                $locales[] = $locale;
            }
        }

        return $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function getActivatedLocales()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getActivatedLocaleCodes()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getActivatedLocalesQB()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getDeletedLocalesForChannel(ChannelInterface $channel)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function countAllActivated()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        throw new NotImplementedException(__METHOD__);
    }
}
