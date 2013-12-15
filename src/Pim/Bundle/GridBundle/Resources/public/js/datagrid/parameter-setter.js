define(
    ['oro/grid/registry', 'oro/mediator'],
    function (registry, mediator) {
        'use strict';

        /**
         * Module to set custom parameters to the datagrid
         */
        return {

            setParameter: function(datagridName, parameterName, parameterValue, refresh) {
                if (!datagridName) {
                    throw new Error('Datagrid name is not specified');
                }

                var datagrid = registry.getElement('datagrid', datagridName);
                if (datagrid) {
                    datagrid.setAdditionalParameter(parameterName, parameterValue);
                    if (refresh === true) {
                        datagrid.getRefreshAction().execute();
                    }
                } else {
                    mediator.once('datagrid:created:' + datagridName, function(datagrid) {
                        datagrid.setAdditionalParameter(parameterName, parameterValue);
                        if (refresh === true) {
                            datagrid.getRefreshAction().execute();
                        }
                    }, this);
                }
            },

            removeParameter: function(datagridName, parameterName, refresh) {
                if (!datagridName) {
                    throw new Error('Datagrid name is not specified');
                }

                var datagrid = registry.getElement('datagrid', datagridName);
                if (datagrid) {
                    datagrid.removeAdditionalParameter(parameterName);
                    if (refresh === true) {
                        datagrid.getRefreshAction().execute();
                    }
                } else {
                    mediator.once('datagrid:created:' + datagridName, function(datagrid) {
                        datagrid.removeAdditionalParameter(parameterName);
                        if (refresh === true) {
                            datagrid.getRefreshAction().execute();
                        }
                    }, this);
                }
            }
        };
    }
);
