/* jshint node:true */
'use strict';

var async = require('async');

var steps = function () {
    this.Given(/^I mass-edit (?:products?|families) (.*)$/, function (entities, callback) {
        entities = this.listToArray(entities);

        var browser = this.browser;

        async.eachSeries(entities, function (entity, cb) {
            browser.execute(
                function (rowText) {
                    /* global $ */
                    var $row = $('table.grid tbody tr td:contains(' + rowText + ')').parent();
                    $row.find('.select-row-cell input:visible').click();
                },
                entity,
                cb
            );
        }, function () {
            browser.execute(
                function () {
                    $('a:contains("Mass Edit")').click();
                }
            ).waitForComplete(callback);
        });
    });

    this.Given(/^I choose the "([^"]*)" operation$/, function (operation, callback) {
        this.browser.execute(
            function (operationLabel) {
                $('label:contains("' + operationLabel + '")').click();
                $('button[title="Next"]').click();
            },
            operation
        ).waitForComplete(callback);
    });

    this.Given(/^I display the (.*) attributes?$/, function (attributes, callback) {
        attributes = this.listToArray(attributes);

        var browser = this.browser;

        browser.execute(
            function () {
                $('button:contains("Select attributes")').click();
            },
            function () {
                async.eachSeries(attributes, function (attribute, cb) {
                    browser.execute(
                        function (attributeLabel) {
                            $('.pimmultiselect input[type="search"]').val(attributeLabel).trigger('keyup');
                            $('.pimmultiselect .ui-multiselect-checkboxes')
                                .find('li:contains("' + attributeLabel + '") label').click();
                        },
                        attribute,
                        cb
                    );
                }, function () {
                    browser.execute(
                        function () {
                            $('.pimmultiselect a.btn:contains("Select")').click();
                        },
                        callback
                    );
                });
            }
        );
    });

    this.Given(/^I move on to the next step$/, function (callback) {
        this.browser
            .click('a.next')
            .waitForComplete()
            .click('button[type="submit"]')
            .waitForComplete(callback);
    });

    this.Given(/^I wait for the "([^"]*)" mass\-edit job to finish$/, function (job, callback) {
        var browser = this.browser;
        this.visit('/job/')
            .waitFor('table.grid', 5000)
            .call(function () {
                var checkStatus = function (cb) {
                    browser.execute(
                        function () {
                            return $('.grid tbody>tr').length === $('.grid tbody>tr:contains("COMPLETED")').length;
                        }, function (err, result) {
                            if (true === result.value) {
                                cb();
                            } else {
                                browser.execute(
                                    function () {
                                        $('a[title="Refresh"]');
                                    }).waitForComplete().call(
                                    function () {
                                        checkStatus(cb);
                                    }
                                );
                            }
                        }
                    );
                };

                checkStatus(callback);
            });
    });
};

module.exports = steps;
