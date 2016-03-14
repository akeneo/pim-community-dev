'use strict';

/**
 * Property accessor implementation in javascript
 * Usage:
 *
 * var object = {
 *     labels: [
 *         {
 *             code: 'awesome'
 *         },
 *         {
 *             code: 'epic'
 *         },
 *         {
 *             code: 'incredible'
 *         }
 *     ],
 *     data: ['nice', 'cool', 'clean']
 * }
 *
 * propertyAccessor.getValue(object, 'labels.1.code') // -> 'epic'
 * propertyAccessor.getValue(object, 'data') // -> ['nice', 'cool', 'clean']
 */
define(
    [],
    function () {
        return {
            getValue: function (objectOrArray, propertyPath) {
                var properties = propertyPath.split('.');

                while (properties.length && (objectOrArray = objectOrArray[properties.shift()]));

                return objectOrArray;
            }
        };
    }
);
