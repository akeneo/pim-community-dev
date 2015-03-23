'use strict';

define(
    ['underscore', 'backbone'],
    function(_, Backbone) {
        return Backbone.View.extend({
            code: 'form',
            initialize: function () {
                this.extensions   = {};
                this.zones        = {};
                this.zone         = '';
                this.insertAction = null;
                this.configured   = false;
            },
            setZones: function (zones) {
                this.zones = zones;
            },
            getTargetElement: function () {
                if ('self' === this.parent.zones[this.zone]) {
                    return this.parent.$el;
                } else {
                    return this.parent.$(this.parent.zones[this.zone]);
                }
            },
            configure: function () {
                var promise = $.Deferred();

                var extensionPromises = [];
                _.each(this.extensions, function(extension) {
                    extensionPromises.push(extension.configure());
                });

                $.when.apply($, extensionPromises).done(_.bind(function() {
                    this.configured = true;
                    promise.resolve();
                }, this));

                return promise.promise();
            },
            addExtension: function (code, extension, zone, insertAction) {
                extension.setParent(this);

                extension.code = code;
                extension.zone = zone;
                extension.insertAction = insertAction || 'append';

                this.extensions[code] = extension;
            },
            setParent: function (parent) {
                this.parent = parent;

                return this;
            },
            getParent: function() {
                return this.parent;
            },
            getRoot: function() {
                var root = this;
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
                _.each(this.extensions, function(extension) {
                    extension.getTargetElement()[extension.insertAction](extension.el);
                    console.log(extension.parent.code, 'triggered the rendering of', extension.code);
                    extension.render();
                });

                return this;
            }
        });
    }
);
