/* global console */
'use strict';

define([
        'module',
        'underscore',
        'pim/form',
        'oro/mediator',
        'pim/fetcher-registry',
        'pim/dialog'
    ],
    function (module, _, BaseForm, mediator, FetcherRegistry, Dialog) {
        return BaseForm.extend({
            configure: function () {
                _.each(module.config().events, _.bind(function (event) {
                    this.listenTo(mediator, event, this.checkStructureVersion);
                }, this));

                this.listenTo(mediator, 'pim_enrich:form:cache:clear', this.clearCache);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            checkStructureVersion: function (entity) {
                if (entity.meta.structure_version !== this.getLocaleStructureVersion(entity.meta.model_type)) {
                    this.clearCache();
                }

                this.setLocaleStructureVersion(entity.meta.model_type, entity.meta.structure_version);
            },
            getLocaleStructureVersion: function (modelType) {
                /*global Storage: true */
                if (!(!_.isUndefined(Storage) && sessionStorage)) {
                    Dialog.alert(_.__('pim_enrich.alert.session_storage.not_available'));
                }

                return parseInt(sessionStorage.getItem('structure_version_' + modelType));
            },
            setLocaleStructureVersion: function (modelType, structureVersion) {
                /*global Storage: true */
                if (!(!_.isUndefined(Storage) && sessionStorage)) {
                    Dialog.alert(_.__('pim_enrich.alert.session_storage.not_available'));
                }

                sessionStorage.setItem('structure_version_' + modelType, structureVersion);
            },
            clearCache: function () {
                console.log('Clear cache !');
                FetcherRegistry.clearAll();
            }
        });
    }
);
