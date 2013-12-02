<?php

namespace Oro\Bundle\LocaleBundle\Form\Type;

use Doctrine\Common\Cache\Cache;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TimezoneType extends AbstractType
{
    /**
     * @var array
     */
    protected static $timezones = null;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param null|Cache $cache
     */
    public function __construct(Cache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $cacheKey = 'timezones';
        if ($this->cache) {
            if ($this->cache->contains($cacheKey)) {
                self::$timezones = $this->cache->fetch($cacheKey);
            } else {
                $this->cache->save($cacheKey, self::getTimezones());
            }
        }

        $resolver->setDefaults(
            array(
                'choices' => self::$timezones,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_locale_timezone';
    }

    /**
     * Returns the timezone choices.
     *
     * The choices are generated from the ICU function
     * \DateTimeZone::listIdentifiers(). They are cached during a single request,
     * so multiple timezone fields on the same page don't lead to unnecessary
     * overhead.
     *
     * @return array The timezone choices
     */
    public static function getTimezones()
    {
        if (null === static::$timezones) {
            static::$timezones = array();

            $timezones = self::getTimezonesData();
            foreach ($timezones as $timezoneData) {
                $timezone = $timezoneData['timezone_id'];
                $offset = $timezoneData['offset'];
                $parts = explode('/', $timezone);

                if (count($parts) > 2) {
                    $region = $parts[0];
                    $name = $parts[1].' - '.$parts[2];
                } elseif (count($parts) > 1) {
                    $region = $parts[0];
                    $name = $parts[1];
                } else {
                    $region = 'Other';
                    $name = $parts[0];
                }

                $timezoneOffset = sprintf(
                    'UTC %+03d:%02u',
                    $offset / 3600,
                    abs($offset) % 3600 / 60
                );
                $timezoneName = '(' . $timezoneOffset . ') ';
                if ($region) {
                    $timezoneName .= $region . '/';
                }
                $timezoneName .= $name;
                static::$timezones[$timezone] = str_replace('_', ' ', $timezoneName);
            }
        }

        return static::$timezones;
    }

    /**
     * Get timezone identifiers with offset sorted by offset and timezone_id.
     *
     * @return array
     */
    public static function getTimezonesData()
    {
        $listIdentifiers = \DateTimeZone::listIdentifiers();
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $timezones = array();
        foreach ($listIdentifiers as $identifier) {
            $timezone = new \DateTimeZone($identifier);
            $timezones[$identifier] = array(
                'offset' => $timezone->getOffset($now),
                'timezone_id' => $identifier
            );
        }

        usort(
            $timezones,
            function ($a, $b) {
                if ($a['offset'] == $b['offset']) {
                    return strcmp($a['timezone_id'], $b['timezone_id']);
                }
                return ($a['offset'] > $b['offset']) ? 1 : -1;
            }
        );
        return $timezones;
    }
}
