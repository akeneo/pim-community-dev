/* jshint node:true */
'use strict';

var steps = function () {
    this.World = require('../support/world.js').World;

    this.Given(/^(?:a|an|the) "([^"]*)" catalog configuration$/, function (catalog, callback) {
        this.executeBehat('a "' + catalog + '" catalog configuration', callback);
    });
};

module.exports = steps;
