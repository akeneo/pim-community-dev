'use strict';

define(
    ['underscore', 'backbone'],
    function(_, Backbone) {
        return Backbone.View.extend({
            initialize: function () {
                this.extensions = [];
            },
            configure: function () {
                var promise = $.Deferred();

                var extensionPromises = [];
                _.each(this.extensions, function(extension) {
                    extensionPromises.push(extension.configure());
                });

                $.when.apply($, extensionPromises).done(function() {
                    promise.resolve();
                });

                return promise.promise();
            },
            addExtension: function (extension) {
                extension.setParent(this);
                this.extensions.push(extension);
            },
            setExtensions: function (extensions) {
                _.each(extensions, _.bind(this.addExtension, this));

                return this;
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
                _.each(this.extensions, function(extension) {
                    console.log(extension.parent.cid, 'triggered the rendering of extension', extension.cid);
                    extension.render();
                });

                return this;
            }
        });
    }
);
