/* jshint node:true */
'use strict';

var steps = function () {
    this.World = require('../support/world.js').World;

    this.Given(/^the following products?:$/, function (products, callback) {
        this.executeBehat('the following products', products, callback);
    });
};

module.exports = steps;
