'use strict';
/**
 * Form main class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/mediator'
    ],
    function (
        $,
        _,
        Backbone,
        mediator
    ) {
        return Backbone.View.extend({
            code: 'form',

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.extensions   = {};
                this.zones        = {};
                this.targetZone   = '';
                this.configured   = false;
            },

            /**
             * Configure the extension and it's child extensions
             *
             * @return {Promise}
             */
            configure: function () {
                var extensionPromises = _.map(this.extensions, function (extension) {
                    return extension.configure();
                });

                return $.when.apply($, extensionPromises).then(function () {
                    this.configured = true;
                }.bind(this));
            },

            /**
             * Add a child extension to this extension
             *
             * @param {string} code      Extension's code
             * @param {object} extension Backbone module of the extension
             * @param {string} zone      Targeted zone
             * @param {int} position     The position of the extension
             */
            addExtension: function (code, extension, zone, position) {
                extension.setParent(this);

                extension.code         = code;
                extension.targetZone   = zone;
                extension.position     = position;

                this.extensions[code] = extension;
            },

            /**
             * Get a child extension (the first extension matching the given code or ends with the given code)
             *
             * @param {string} code
             *
             * @return {object}
             */
            getExtension: function (code) {
                return this.extensions[_.findKey(this.extensions, function(extension) {
                    var expectedPosition = extension.code.length - code.length;

                    return expectedPosition >= 0 && expectedPosition === extension.code.indexOf(code, expectedPosition);
                })];
            },

            /**
             * Set the parent of this extension
             *
             * @param {object} parent
             */
            setParent: function (parent) {
                this.parent = parent;

                return this;
            },

            /**
             * Get the parent of the extension
             *
             * @return {object}
             */
            getParent: function () {
                return this.parent;
            },

            /**
             * Get the root extension
             *
             * @return {object}
             */
            getRoot: function () {
                /* jscs:disable safeContextKeyword */
                var root = this;
                /* jscs:enable safeContextKeyword */
                var parent = this.getParent();
                while (parent) {
                    root = parent;
                    parent = parent.getParent();
                }

                return root;
            },

            /**
             * Set data in the root model
             *
             * @param {object} data
             * @param {object} options If silent is setted to true, don't fire events
             *                         pim_enrich:form:entity:pre_update and pim_enrich:form:entity:post_update
             */
            setData: function (data, options) {
                options = options || {};

                if (!options.silent) {
                    mediator.trigger('pim_enrich:form:entity:pre_update', data);
                }

                this.getRoot().model.set(data, options);

                if (!options.silent) {
                    mediator.trigger('pim_enrich:form:entity:post_update', data);
                }

                return this;
            },

            /**
             * Get the form raw data (vanila javascript object)
             *
             * @return {object}
             */
            getFormData: function () {
                return this.getRoot().model.toJSON();
            },

            /**
             * Get the form data (backbone model)
             *
             * @return {object}
             */
            getFormModel: function () {
                return this.getRoot().model;
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                return this.renderExtensions();
            },

            /**
             * Render the child extensions
             *
             * @return {object}
             */
            renderExtensions: function () {
                this.initializeDropZones();

                _.each(this.extensions, function (extension) {
                    this.getZone(extension.targetZone).appendChild(extension.el);
                    this.renderExtension(extension);
                }.bind(this));

                return this;
            },

            /**
             * Render a single extension
             *
             * @param {object} extension
             *
             * @return {object}
             */
            renderExtension: function (extension) {
                /* global console */
                console.log(extension.parent.code, 'triggered the rendering of', extension.code);
                return extension.render();
            },

            /**
             * Initialize dropzone cache
             */
            initializeDropZones: function () {
                this.zones = _.indexBy(this.$('[data-drop-zone]'), function (zone) {
                    return zone.dataset.dropZone;
                });

                this.zones.self = this.el;
            },

            /**
             * Get the drop zone for the given code
             *
             * @param {string} code
             *
             * @return {jQueryElement}
             */
            getZone: function (code) {
                if (!(code in this.zones)) {
                    this.zones[code] = this.$('[data-drop-zone="' + code + '"]')[0];
                }

                if (!this.zones[code]) {
                    throw new Error('Zone "' + code + '" does not exist');
                }

                return this.zones[code];
            },

            /**
             * Trigger event on each child extensions and their childs
             */
            triggerExtensions: function () {
                var options = _.toArray(arguments);

                _.each(this.extensions, function (extension) {
                    extension.trigger.apply(extension, options);
                    extension.triggerExtensions.apply(extension, options);
                });
            },

            /**
             * Listen on child extensions and their childs events
             *
             * @param {string}   code
             * @param {Function} callback
             */
            onExtensions: function (code, callback) {
                _.each(this.extensions, function (extension) {
                    this.listenTo(extension, code, callback);
                }.bind(this));
            }
        });
    }
);
