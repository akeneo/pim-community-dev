'use strict';

define(
    ['underscore', 'backbone'],
    function(_, Backbone) {
        return Backbone.View.extend({
            code: '',
            initialize: function () {
                this.extensions = {};
                this.configured = false;
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
            addExtension: function (code, extension) {
                extension.setParent(this);
                extension.code = code;
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
            setData: function (data) {
                this.getRoot().model.set(data);

                return this;
            },
            getData: function () {
                return this.getRoot().model.toJSON();
            },
            render: function () {
                if (!this.configured) {
                    return;
                }

                _.each(this.extensions, function(extension) {
                    console.log(extension.parent.cid, 'triggered the rendering of extension', extension.cid);
                    extension.render();
                });

                return this;
            }
        });
    }
);
