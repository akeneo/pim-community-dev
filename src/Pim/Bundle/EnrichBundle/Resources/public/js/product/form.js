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
            setZones: function (zones) {
                this.zones = zones;
            },
            getTargetElement: function () {
                if ('self' === this.parent.zones[this.targetZone]) {
                    return this.parent.$el;
                } else {
                    return this.parent.$(this.parent.zones[this.targetZone]);
                }
            },
            configure: function () {
                return $.when(_.map(this.extensions, function (extension) {
                        return extension.configure();
                    }))
                    .done(_.bind(function () {
                        this.configured = true;
                    }, this)
                );
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
                var sortedExtensions = _.sortBy(this.extensions, 'position');
                _.each(sortedExtensions, _.bind(function (extension) {
                    this.renderExtension(extension);
                }, this));

                return this;
            },
            renderExtension: function (extension) {
                extension.getTargetElement().append(extension.el);
                /* global console */
                console.log(extension.parent.code, 'triggered the rendering of', extension.code);
                extension.render();
            }
        });
    }
);
