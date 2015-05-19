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

    this.Given(/^I am on the "([^"]*)" variant group page$/, function (code, callback) {
        this
            .visit('#url=/enrich/variant-group/')
            .waitFor('.row-click-action', 5000)
            .click('//tr//td[normalize-space() = "' + code + '"]')
            .waitForComplete(callback);
    });

    this.When(/^I am on the variant groups page$/, function (callback) {
        this
            .visit('#url=/enrich/variant-group/')
            .waitForComplete(callback);
    });

    this.Given(/^I am on the products page$/, function (callback) {
        this
            .visit('/enrich/product/')
            .waitForComplete(callback);
    });

    this.When(/^(?:I edit the|I am on the) "([^"]*)" product(?: page)?$/, function (code, callback) {
        this
            .visit('/enrich/product/')
            .waitFor('.row-click-action', 5000)
            .click('//tr//td[normalize-space() = "' + code + '"]')
            .waitForComplete(callback);
    });
};

module.exports = steps;
