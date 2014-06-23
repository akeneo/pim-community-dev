<?php

namespace Akeneo\Bundle\MeasureBundle\Family;

/**
 * Voltage measures constants
 *
 * @author karec
 */
interface VoltageFamilyInterface {
    
    /**
     * Family measure name
     * @staticvar string
     */
    const FAMILY = 'Voltage';

    /**
     * @staticvar string
     */
    const MILLIVOLT = 'MILLIVOLT';
    
    /**
     * @staticvar string
     */
    const CENTIVOLT = 'CENTIVOLT';
    
    /**
     * @staticvar string
     */
    const DECIVOLT = 'DECIVOLT';
    
    /**
     * @staticvar string
     */
    const VOLT = 'VOLT';
    
    /**
     * @staticvar string
     */
    const DEKAVOLT = 'DEKAVOLT';
    
    /**
     * @staticvar string
     */
    const HECTOVOLT = 'HECTOVOLT';
    
    /**
     * @staticvar string
     */
    const KILOVOLT = 'KILOVOLT';
}
