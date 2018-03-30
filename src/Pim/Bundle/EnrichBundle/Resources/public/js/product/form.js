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
            parent: null,
            preUpdateEventName: 'pim_enrich:form:entity:pre_update',
            postUpdateEventName: 'pim_enrich:form:entity:post_update',

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.extensions = {};
                this.zones      = {};
                this.targetZone = '';
                this.configured = false;
            },

            /**
             * Configure the extension and its child extensions
             *
             * @return {Promise}
             */
            configure: function () {
                if (null === this.parent) {
                    this.model = new Backbone.Model();
                }

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
             * @param {Object} extension Backbone module of the extension
             * @param {string} zone      Targeted zone
             * @param {int} position     The position of the extension
             */
            addExtension: function (code, extension, zone, position) {
                extension.setParent(this);

                extension.code       = code;
                extension.targetZone = zone;
                extension.position   = position;

                if ((undefined === this.extensions) || (null === this.extensions)) {
                    throw 'this.extensions have to be defined. Please ensure you called initialize() method.';
                }

                this.extensions[code] = extension;
            },

            /**
             * Get a child extension (the first extension matching the given code or ends with the given code)
             *
             * @param {string} code
             *
             * @return {Object}
             */
            getExtension: function (code) {
                return this.extensions[_.findKey(this.extensions, function (extension) {
                    var expectedPosition = extension.code.length - code.length;

                    return expectedPosition >= 0 && expectedPosition === extension.code.indexOf(code, expectedPosition);
                })];
            },

            /**
             * Set the parent of this extension
             *
             * @param {Object} parent
             */
            setParent: function (parent) {
                this.parent = parent;

                return this;
            },

            /**
             * Get the parent of the extension
             *
             * @return {Object}
             */
            getParent: function () {
                return this.parent;
            },

            /**
             * Get the root extension
             *
             * @return {Object}
             */
            getRoot: function () {
                var rootView = this;
                var parent = this.getParent();
                while (parent) {
                    rootView = parent;
                    parent = parent.getParent();
                }

                return rootView;
            },

            /**
             * Set data in the root model
             *
             * @param {Object} data
             * @param {Object} options If silent is set to true, don't fire events
             *                         pim_enrich:form:entity:pre_update and pim_enrich:form:entity:post_update
             */
            setData: function (data, options = {}) {
                options = options || {};

                if (!options.silent) {
                    this.getRoot().trigger(this.preUpdateEventName, data);
                }

                this.getRoot().model.set(data, options);

                if (!options.silent) {
                    this.getRoot().trigger(this.postUpdateEventName, data);
                }

                return this;
            },

            /**
             * Get the form raw data (vanilla javascript object)
             *
             * @return {Object}
             */
            getFormData: function () {
                return this.getRoot().model.toJSON();
            },

            /**
             * Get the form data (backbone model)
             *
             * @return {Object}
             */
            getFormModel: function () {
                return this.getRoot().model;
            },

            /**
             * Called before removing the form from the view
             */
            shutdown: function () {
                this.doShutdown();

                _.each(this.extensions, (extension) => {
                    extension.shutdown();
                });
            },

            /**
             * The actual shutdown method called on all extensions
             */
            doShutdown: function () {
                this.stopListening();
                this.undelegateEvents();
                this.$el.removeData().off();
                this.remove();
                Backbone.View.prototype.remove.call(this);
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
             * @return {Object}
             */
            renderExtensions: function () {
                // If the view is no longer attached to the DOM, don't render the extensions
                if (undefined === this.el) {
                    return this;
                }

                this.initializeDropZones();

                _.each(this.extensions, function (extension) {
                    this.renderExtension(extension);
                }.bind(this));

                return this;
            },

            /**
             * Render a single extension
             *
             * @param {Object} extension
             */
            renderExtension: function (extension) {
                var zone = this.getZone(extension.targetZone);

                if (null === zone) {
                    throw new Error('Can not render extension "' + extension.code + '" in "' + this.code + '": ' +
                        'zone "' + extension.targetZone + '" does not exist');
                }

                zone.appendChild(extension.el);

                extension.render();
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
             * @param {string|null} code
             *
             * @return {jQueryElement}
             */
            getZone: function (code) {
                if (!(code in this.zones)) {
                    this.zones[code] = this.$('[data-drop-zone="' + code + '"]')[0];
                }

                if (!this.zones[code]) {
                    return null;
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
            },

            /**
             * Get the root form code
             *
             * @return {string}
             */
            getFormCode: function () {
                return this.getRoot().code;
            },

            /**
             * Listen to given mediator events to trigger them locally (in the local root).
             * This way, extensions attached to this form don't have to listen "globally" on the mediator.
             *
             * @param {Array} mediator events to forward:
             *                [ {'mediator:event:name': 'this:event:name'}, {...} ]
             */
            forwardMediatorEvents: function (events) {
                _.each(events, function (localEvent, mediatorEvent) {
                    this.listenTo(mediator, mediatorEvent, function (data) {
                        this.trigger(localEvent, data);
                    });
                }.bind(this));
            }
        });
    }
);
