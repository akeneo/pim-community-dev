/* jshint node:true */
'use strict';

var steps = function () {
    this.World = require('../support/world.js').World;

    this.Given(/^I launched the completeness calculator$/, function (callback) {
        this.executeBehat('I launched the completeness calculator', callback);
    });
};

module.exports = steps;
