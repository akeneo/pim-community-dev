/* jshint node:true */
'use strict';

var steps = function () {
    this.World = require('../support/world.js').World;

    this.Given(/^the following products?:$/, function (products, callback) {
        this.executeBehat('the following products', products, callback);
    });

    this.Given(/^the following variant group values:$/, function (values, callback) {
        this.executeBehat('the following variant group values', values, callback);
    });

    this.Given(/^the following attributes:$/, function (attributes, callback) {
        this.executeBehat('the following attributes', attributes, callback);
    });

    this.Given(/^the following product groups?:$/, function (groups, callback) {
        this.executeBehat('the following product groups', groups, callback);
    });

    this.Given(/^the following family:$/, function (data, callback) {
        this.executeBehat('the following family', data, callback);
    });
};

module.exports = steps;
