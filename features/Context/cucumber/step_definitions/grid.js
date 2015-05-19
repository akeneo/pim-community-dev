/* jshint node:true */
'use strict';

var assert = require('assert');
var async = require('async');

var steps = function () {
    this.World = require('../support/world.js').World;

    this.Then(/^the grid should contain (\d+) elements$/, function (count, callback) {
        this.browser
            .waitFor('table.grid', 5000)
            .elements('table.grid tbody tr', function (err, res) {
                assert.equal(
                    res.value.length,
                    count,
                    'Expected to see ' + count + ' rows in the grid, found ' + res.value.length
                );
            }).call(callback);
    });

    this.Then(/^I should see products? (.*)$/, function (products, callback) {
        var browser = this.browser;
        products = this.listToArray(products);
        if (products.length > 10) {
            throw new Error('Please implement changing the page size');
        }
        async.each(products, function (product, cb) {
            browser.element('//tr//td[normalize-space() = "' + product + '"]', function (err, element) {
                assert.notEqual(element, null, 'Expecting to see product ' + product + ', not found!');
                cb();
            });
        }, callback);
    });

    this.Then(/^I check the row "([^"]*)"$/, function (row, callback) {
        this.browser.execute(
            function (rowText) {
                /* global $ */
                var $row = $('table.grid tbody tr td:contains(' + rowText + ')').parent();
                $row.find('.boolean-cell input:visible').click();
            },
            row,
            callback
        );
    });

    this.Then(/^the row "([^"]*)" should be checked$/, function (row, callback) {
        this.browser.execute(
            function (rowText) {
                /* global $ */
                var $row = $('table.grid tbody tr td:contains(' + rowText + ')').parent();
                var $input = $row.find('.boolean-cell input:visible');
                return $input.is(':checked');
            },
            row,
            function (err, result) {
                assert.equal(result.value, true, 'Expecting the row ' + row + ' to be checked');
                callback();
            }
        );
    });

    this.When(/^I click on the "([^"]*)" action of the row which contains "([^"]*)"$/,
        function (action, row, callback) {
            var browser = this.browser;
            this.browser.execute(function (actionText, rowText) {
                var $row = $('table.grid tbody tr td:contains(' + rowText + ')').parent();
                $row.find('a.action[title="' + actionText + '"]').click();
            }, action, row, function () {
                browser.waitForComplete(callback);
            });
        }
    );
};

module.exports = steps;
