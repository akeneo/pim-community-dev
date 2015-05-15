/* jshint node:true */
'use strict';

var steps = function () {
    this.World = require('../support/world.js').World;

    this.Given(/^I visit the "([^"]*)" tab$/, function (tab, callback) {
        this.browser
            .waitFor('#form-navbar', 5000)
            .element('#form-navbar').click('=' + tab, callback);
    });

    this.When(/^I visit the "([^"]*)" group$/, function (group, callback) {
        this.browser
            .waitFor('.tab-groups', 5000)
            .element('.tab-groups').click('=' + group, callback);
    });

    // Price input
    this.When(/^I change the "(?:(\$|\€)([^"]*))" to "([^"]*)"$/, function (currency, attribute, value, callback) {
        this.browser
            .waitFor('.attribute-field.currency')
            .setValue('.attribute-field.currency input.input-small', value, callback);
    });

    this.When(/^I save the variant group$/, function (callback) {
        this.browser.click('button[type="submit"]', callback);
    });

    this.Then(/^I press the "([^"]*)" button$/, function (name, callback) {
        var browser = this.browser;
        browser
            .execute(function (button) {
                /* global $ */
                $('button.btn-primary:contains("' + button + '")').click();
            }, name, function () {
                browser.waitForComplete(callback);
            });
    });

    this.When(/^I confirm the deletion$/, function (callback) {
        var browser = this.browser;
        browser
            .execute(function () {
                $('.modal .btn.ok').click();
            }, function () {
                browser.waitForComplete(callback);
            });
    });

    this.Then(/^I open the history$/, function (callback) {
        this.browser
            .waitFor('button[data-panel="history"]', 5000)
            .click('button[data-panel="history"]')
            .waitFor('button.expand-history', 5000)
            .click('button.expand-history', callback);
    });
};

module.exports = steps;
