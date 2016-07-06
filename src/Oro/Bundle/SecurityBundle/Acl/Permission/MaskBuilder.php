<?php

namespace Oro\Bundle\SecurityBundle\Acl\Permission;

/**
 * The base abstract class for different sort of permission mask builders which allows you
 * to build cumulative permissions easily, or convert masks to a human-readable format.
 *
 * Usually when you create own mask builder you just need define MASK_* and CODE_*
 * constants in your class. Also you can redefine PATTERN_ALL_* constants if you want to
 * change the human-readable format of a bitmask created by your mask builder.
 *
 * For example if a mask builder defines the following constants:
 * <code>
 *       const MASK_VIEW = 1;
 *       const MASK_EDIT = 2;
 *       const CODE_VIEW = 'V';
 *       const CODE_EDIT = 'E';
 *       const PATTERN_ALL_OFF = '............................ example:....';
 * </code>
 * it can be used in way like this:
 * <code>
 *       $builder
 *           ->add('view');
 *           ->add('edit');
 *
 *       // int(3)
 *       var_dump($builder->get());
 *       // string(41) "............................ example:..EV"
 *       var_dump($builder->getPattern());
 *       // string(32) "..............................EV"
 *       var_dump($builder->getPattern(true));
 * </code>
 */
abstract class MaskBuilder
{
    /**
     * Defines a human-readable format of a bitmask
     * All characters are allowed here, but only a character defined in self::OFF constant
     * is interpreted as bit placeholder.
     */
    const PATTERN_ALL_OFF = '................................';

    /**
     * Defines the brief form of a human-readable format of a bitmask
     */
    const PATTERN_ALL_OFF_BRIEF = '................................';

    /**
     * A symbol is used in PATTERN_ALL_* constants as a placeholder of a bit
     */
    const OFF = '.';

    /**
     * The default character is used in a human-readable format to show that a bit in the bitmask is set
     * If you want more readable character please define CODE_* constants in your mask builder class.
     */
    const ON = '*';

    protected $mask;

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->reset();
    }

    /**
     * Gets the mask of this permission
     *
     * @return integer
     */
    public function get()
    {
        return $this->mask;
    }

    /**
     * Adds a mask to the permission
     *
     * @param int|string $mask
     * @throws \InvalidArgumentException
     * @return MaskBuilder
     */
    public function add($mask)
    {
        if (is_string($mask)) {
            $name = 'static::MASK_' . strtoupper($mask);
            if (!defined($name)) {
                throw new \InvalidArgumentException(sprintf('Undefined mask: %s.', $mask));
            }
            $mask = constant($name);
        } elseif (!is_int($mask)) {
            throw new \InvalidArgumentException('$mask must be a string or an integer.');
        }

        $this->mask |= $mask;

        return $this;
    }

    /**
     * Removes a mask from the permission
     *
     * @param int|string $mask
     * @throws \InvalidArgumentException
     * @return MaskBuilder
     */
    public function remove($mask)
    {
        if (is_string($mask) && defined($name = 'static::MASK_' . strtoupper($mask))) {
            $mask = constant($name);
        } elseif (!is_int($mask)) {
            throw new \InvalidArgumentException('$mask must be a string or an integer.');
        }

        $this->mask &= ~$mask;

        return $this;
    }

    /**
     * Resets the builder
     *
     * @return MaskBuilder
     */
    public function reset()
    {
        $this->mask = 0;

        return $this;
    }

    /**
     * Gets a human-readable representation of this mask
     *
     * @param bool $brief optional; defaults to false
     *                    Determine whether the representation should be in brief of full format
     * @return string
     */
    public function getPattern($brief = false)
    {
        return static::getPatternFor($this->mask, $brief);
    }

    /**
     * Gets a human-readable representation of the given mask
     *
     * @param int $mask
     * @param bool $brief optional; defaults to false
     *                    Determine whether the representation should be in brief of full format
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function getPatternFor($mask, $brief = false)
    {
        if (!is_int($mask)) {
            throw new \InvalidArgumentException('$mask must be an integer.');
        }

        $pattern = $brief ? static::PATTERN_ALL_OFF_BRIEF : static::PATTERN_ALL_OFF;
        $length = strlen(static::PATTERN_ALL_OFF_BRIEF);
        $bitmask = str_pad(decbin($mask), $length, '0', STR_PAD_LEFT);

        for ($i = $length - 1, $p = strlen($pattern) - 1; $i >= 0; $i--, $p--) {
            // skip non mask chars if any
            while ($p >= 0 && static::OFF !== $pattern[$p]) {
                $p--;
            }
            if ('1' === $bitmask[$i]) {
                $pattern[$p] = static::getCode(1 << ($length - $i - 1));
            }
        }

        return $pattern;
    }

    /**
     * Gets the code for the passed mask
     *
     * @param integer $mask
     * @return string
     */
    protected static function getCode($mask)
    {
        $reflection = new \ReflectionClass(get_called_class());
        foreach ($reflection->getConstants() as $name => $cMask) {
            if (0 !== strpos($name, 'MASK_')) {
                continue;
            }

            if ($mask === $cMask) {
                $cName = 'static::CODE_' . substr($name, 5);
                if (defined($cName)) {
                    return constant($cName);
                }
                $lastDelim = strrpos($name, '_');
                if ($lastDelim > 5) {
                    $cName = 'static::CODE_' . substr($name, 5, $lastDelim - 5);
                    if (defined($cName)) {
                        return constant($cName);
                    }
                }
            }
        }

        return static::ON;
    }

    /**
     * Checks whether a constant with the given name is defined in this mask builder
     *
     * @param string $name
     * @return mixed
     */
    public static function hasConst($name)
    {
        return defined('static::' . $name);
    }

    /**
     * Gets constant value by its name
     *
     * @param string $name
     * @return mixed
     */
    public static function getConst($name)
    {
        return constant('static::' . $name);
    }
}
