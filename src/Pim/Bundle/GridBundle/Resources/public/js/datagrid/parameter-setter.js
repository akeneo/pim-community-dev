define(
    ['oro/registry', 'oro/mediator'],
    function (registry, mediator) {
        'use strict';

        /**
         * Module to set custom parameters to the datagrid
         */
        return {

            setParameter: function(datagridName, parameterName, parameterValue) {
                if (!datagridName) {
                    throw new Error('Datagrid name is not specified');
                }

                var datagrid = registry.getElement('datagrid', datagridName);
                if (datagrid) {
                    datagrid.setAdditionalParameter(parameterName, parameterValue);
                } else {
                    mediator.once("datagrid:created:" + datagridName, function(datagrid) {
                        datagrid.setAdditionalParameter(parameterName, parameterValue);
                    }, this);
                }
            },

            removeParameter: function(datagridName, parameterName) {
                if (!datagridName) {
                    throw new Error('Datagrid name is not specified');
                }

                var datagrid = registry.getElement('datagrid', datagridName);
                if (datagrid) {
                    datagrid.removeAdditionalParameter(parameterName);
                } else {
                    mediator.once("datagrid:created:" + datagridName, function(datagrid) {
                        datagrid.removeAdditionalParameter(parameterName);
                    }, this);
                }
            }
        };
    }
);
