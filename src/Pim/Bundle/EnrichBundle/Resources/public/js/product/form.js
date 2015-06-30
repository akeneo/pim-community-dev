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
    ['jquery', 'underscore', 'backbone'],
    function ($, _, Backbone) {
        return Backbone.View.extend({
            code: 'form',
            initialize: function () {
                this.extensions   = {};
                this.zones        = {};
                this.targetZone   = '';
                this.configured   = false;
            },
            configure: function () {
                var extensionPromises = _.map(this.extensions, function (extension) {
                    return extension.configure();
                });

                return $.when.apply($, extensionPromises).then(_.bind(function () {
                    this.configured = true;
                }, this));
            },
            addExtension: function (code, extension, zone, position) {
                extension.setParent(this);

                extension.code         = code;
                extension.targetZone   = zone;
                extension.position     = position;

                this.extensions[code] = extension;
            },
            setParent: function (parent) {
                this.parent = parent;

                return this;
            },
            getParent: function () {
                return this.parent;
            },
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
            setData: function (data, options) {
                options = options || {};

                this.getRoot().model.set(data, options);

                return this;
            },
            getData: function () {
                return this.getRoot().model.toJSON();
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                return this.renderExtensions();
            },
            renderExtensions: function () {
                this.initializeDropZones();

                _.each(this.extensions, _.bind(function (extension) {
                    this.getZone(extension.targetZone).appendChild(extension.el);
                    this.renderExtension(extension);
                }, this));

                return this;
            },
            renderExtension: function (extension) {
                /* global console */
                console.log(extension.parent.code, 'triggered the rendering of', extension.code);
                return extension.render();
            },
            initializeDropZones: function () {
                this.zones = _.indexBy(this.$('[data-drop-zone]'), function (zone) {
                    return zone.dataset.dropZone;
                });

                this.zones.self = this.el;
            },
            getZone: function (code) {
                if (!(code in this.zones)) {
                    this.zones[code] = this.$('[data-drop-zone="' + code + '"]')[0];
                }

                if (!this.zones[code]) {
                    throw new Error('Zone "' + code + '" does not exist');
                }

                return this.zones[code];
            },
            triggerExtensions: function () {
                var options = _.toArray(arguments);

                _.each(this.extensions, function (extension) {
                    extension.trigger.apply(extension, options);
                    extension.triggerExtensions.apply(extension, options);
                });
            },
            onExtensions: function (code, callback) {
                _.each(this.extensions, _.bind(function (extension) {
                    this.listenTo(extension, code, callback);
                }, this));
            }
        });
    }
);
