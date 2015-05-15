/* jshint node:true */
'use strict';

var async = require('async');
var assert = require('assert');

var steps = function () {
    this.World = require('../support/world.js').World;

    this.Then(/^the product "([^"]*)" should have the following values:$/, function (product, values, callback) {
        this.executeBehat('the product "' + product + '" should have the following values', values, callback);
    });

    this.Then(/^the locale switcher should contain the following items:$/, function (table, callback) {
        var browser = this.browser;

        async.eachSeries(table.hashes(), function (hash, cb) {
            browser.execute(
                function (hash) {
                    /* global $ */
                    var $localeSwitcherContainer = $('div.locale-switcher');
                    if (!$localeSwitcherContainer.length) {
                        return 'locale switcher not found';
                    }

                    var $localeLink = $localeSwitcherContainer.find('a[data-locale="' + hash.locale + '"]');
                    if (!$localeLink.length) {
                        return 'locale "' + hash.locale + '" not found';
                    }

                    return true;
                },
                hash,
                function (err, result) {
                    assert.equal(result.value, true, result.value);
                    cb();
                }
            );
        }, callback);
    });

    this.Then(/^the product "([^"]+)" should be "([^"]+)"$/, function (attributeCode, attributeValue, callback) {
        var browser = this.browser;

        browser.execute(
            function (attributeCode, attributeValue) {
                var $fieldContainer = $('div[data-attribute="' + attributeCode + '"]');
                if (!$fieldContainer.length) {
                    return 'No container found for attribute ' + attributeCode;
                }

                var foundValue = $fieldContainer.find('.field-input input').val();
                if (foundValue !== attributeValue) {
                    return 'Expected attribute "' + attributeCode + '"' +
                        ' to be "' + attributeValue + '"' +
                        ', got ' + foundValue;
                }

                return true;
            },
            attributeCode,
            attributeValue,
            function (err, result) {
                assert.equal(result.value, true, result.value);
                callback();
            }
        );
    });

    this.Then(/^I should see history:$/, function (table, callback) {
        var browser = this.browser;

        async.eachSeries(table.hashes(), function (hash, cb) {
            browser.execute(
                function (hash) {
                    /* global $ */
                    var $row = $('tr[data-version="' + hash.version + '"]');
                    if (!$row.length) {
                        return 'No row found for version ' + hash.version;
                    }
                    if (!$row.hasClass('expanded')) {
                        $row.click();
                    }
                    var author = $row.find('td.author').text();
                    if (author !== hash.author) {
                        return 'Expected the author of version ' + hash.version +
                            ' to be ' + hash.author + ', got ' + author;
                    }
                    var $changesetRow = $row.next().find('tbody tr').filter(function (i, row) {
                        return $(row).find('.property').text() === hash.property;
                    });

                    return $changesetRow.find('.new-values').text() === hash.value ?
                        true :
                        'Expected the new value of version ' + hash.version +
                            ' to be ' + hash.value + ', got ' + $changesetRow.find('.new-values').text();
                },
                hash,
                function (err, result) {
                    assert.equal(result.value, true, result.value);
                    cb();
                }
            );
        }, callback);
    });

    this.Then(/^I should see "((?:[^"]|\\")+)"$/, function (text, callback) {
        this.browser
            .execute(
                function (text) {
                    return $('*:contains("' + text + '")').length;
                },
                text.replace(/\\"/g, '"'),
                function (err, response) {
                    assert(response.value > 0, 'Expected to see text"' + text + '", not found');
                    callback();
                }
            );
    });
};

module.exports = steps;
