/* jshint node:true */
'use strict';

var steps = function () {
    this.World = require('../support/world.js').World;

    this.Given(/^I am logged in as "([^"]*)"$/, function (user, callback) {
        this.visit('user/login')
            .setValue('#prependedInput', user)
            .setValue('#prependedInput2', user)
            .submitForm('.form-signin', callback);
    });

    this.When(/^I am on the dashboard page$/, function (callback) {
        this.visit('', callback);
    });
};

module.exports = steps;
