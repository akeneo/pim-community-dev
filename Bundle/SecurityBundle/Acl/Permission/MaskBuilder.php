<?php

namespace Oro\Bundle\SecurityBundle\Acl\Permission;

/**
 * This class allows you to build cumulative permissions easily, or convert
 * masks to a human-readable format.
 *
 * <code>
 *       $builder = new MaskBuilder();
 *       $builder
 *           ->add('view_deep')
 *           ->add('create_basic')
 *           ->add('edit_local')
 *       ;
 *       // int(4354) = 4096 + 2 + 256
 *       var_dump($builder->get());
 *       // string(72) "(SADECV) ........ global:...... deep:.....V local:...E.. basic:....C."
 *       var_dump($builder->getPattern());
 *       // string(32) "...................V...E......C."
 *       var_dump($builder->getPattern(true));
 * </code>
 */
class MaskBuilder
{
    // These access levels give a user access to own records and objects that are shared with the user.
    const MASK_VIEW_BASIC         = 1;         // 1 << 0
    const MASK_CREATE_BASIC       = 2;         // 1 << 1
    const MASK_EDIT_BASIC         = 4;         // 1 << 2
    const MASK_DELETE_BASIC       = 8;         // 1 << 3
    const MASK_ASSIGN_BASIC       = 16;        // 1 << 4
    const MASK_SHARE_BASIC        = 32;        // 1 << 5

    // These access levels give a user access to records in the user's business unit.
    const MASK_VIEW_LOCAL         = 64;        // 1 << 6
    const MASK_CREATE_LOCAL       = 128;       // 1 << 7
    const MASK_EDIT_LOCAL         = 256;       // 1 << 8
    const MASK_DELETE_LOCAL       = 512;       // 1 << 9
    const MASK_ASSIGN_LOCAL       = 1024;      // 1 << 10
    const MASK_SHARE_LOCAL        = 2048;      // 1 << 11

    // These access levels give a user access to records in the user's business unit
    // and all business units subordinate to the user's business unit.
    const MASK_VIEW_DEEP          = 4096;      // 1 << 12
    const MASK_CREATE_DEEP        = 8192;      // 1 << 13
    const MASK_EDIT_DEEP          = 16384;     // 1 << 14
    const MASK_DELETE_DEEP        = 32768;     // 1 << 15
    const MASK_ASSIGN_DEEP        = 65536;     // 1 << 16
    const MASK_SHARE_DEEP         = 131072;    // 1 << 17

    // These access levels give a user access to all records within the organization,
    // regardless of the business unit hierarchical level to which the instance or the user belongs.
    const MASK_VIEW_GLOBAL        = 262144;    // 1 << 18
    const MASK_CREATE_GLOBAL      = 524288;    // 1 << 19
    const MASK_EDIT_GLOBAL        = 1048576;   // 1 << 20
    const MASK_DELETE_GLOBAL      = 2097152;   // 1 << 21
    const MASK_ASSIGN_GLOBAL      = 4194304;   // 1 << 22
    const MASK_SHARE_GLOBAL       = 8388608;   // 1 << 23

    const CODE_VIEW         = 'V';
    const CODE_CREATE       = 'C';
    const CODE_EDIT         = 'E';
    const CODE_DELETE       = 'D';
    const CODE_ASSIGN       = 'A';
    const CODE_SHARE        = 'S';

    const ALL_OFF           = '(SADECV) ........ global:...... deep:...... local:...... basic:......';
    const ALL_OFF_BRIEF     = '................................';
    const OFF               = '.';
    const ON                = '*';

    private $mask;

    /**
     * Constructor
     *
     * @param integer $mask optional; defaults to 0
     * @throws \InvalidArgumentException
     */
    public function __construct($mask = 0)
    {
        if (!is_int($mask)) {
            throw new \InvalidArgumentException('$mask must be an integer.');
        }

        $this->mask = $mask;
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
     * @param mixed $mask
     * @return MaskBuilder
     * @throws \InvalidArgumentException
     */
    public function add($mask)
    {
        if (is_string($mask) && defined($name = 'static::MASK_'.strtoupper($mask))) {
            $mask = constant($name);
        } elseif (!is_int($mask)) {
            throw new \InvalidArgumentException('$mask must be an integer.');
        }

        $this->mask |= $mask;

        return $this;
    }

    /**
     * Removes a mask from the permission
     *
     * @param mixed $mask
     * @return MaskBuilder
     * @throws \InvalidArgumentException
     */
    public function remove($mask)
    {
        if (is_string($mask) && defined($name = 'static::MASK_'.strtoupper($mask))) {
            $mask = constant($name);
        } elseif (!is_int($mask)) {
            throw new \InvalidArgumentException('$mask must be an integer.');
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
        $pattern = $brief ? self::ALL_OFF_BRIEF : self::ALL_OFF;
        $length = strlen(self::ALL_OFF_BRIEF);
        $bitmask = str_pad(decbin($this->mask), $length, '0', STR_PAD_LEFT);

        for ($i = $length - 1, $p = strlen($pattern) - 1; $i >= 0; $i--, $p--) {
            // skip non mask chars if any
            while ($p >=0 && self::OFF !== $pattern[$p]) {
                $p--;
            }
            if ('1' === $bitmask[$i]) {
                $pattern[$p] = self::getCode(1 << ($length - $i - 1));
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
                if (strrpos($name, '_') > 5) {
                    $cName = 'static::CODE_' . substr($name, 5, strrpos($name, '_') - 5);
                    if (defined($cName)) {
                        return constant($cName);
                    }
                }
            }
        }

        return self::ON;
    }
}
