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
        var browser = this.browser;
        browser.element('.tab-groups', function (err) {
            if (err) {
                return browser.click(
                    '.attribute-group-selector [data-attribute-group="' + group.toLowerCase() + '"]',
                    callback
                );
            }
            browser.click('=' + group, callback);
        });
    });

    // Price input
    this.When(/^I change the "(\$|\€) ([^"]*)" to "([^"]*)"$/, function (currency, attribute, value, callback) {
        this.browser
            .waitFor('.attribute-field.currency')
            .execute(
                function (currencySymbol, attributeLabel, value) {
                    /* global $ */
                    var $field = $('.attribute-field.currency label:contains("' + attributeLabel + '")').parent();
                    var fieldIndex;
                    $field.find('.currency-label').each(function (index, subLabel) {
                        if ($(subLabel).text() === currencySymbol) {
                            fieldIndex = index;
                        }
                    });
                    $field.find('.controls input.input-small').eq(fieldIndex).val(value).trigger('change');
                },
                currency,
                attribute,
                value,
                callback
            );
    });

    this.When(/^I change the "([^"\$\€]*)" to "([^"]+)"$/, function (field, value, callback) {
        this.browser.execute(
            function (fieldName, value) {
                var $field = $('div [data-attribute="' + fieldName + '"] .field-input');

                var $inputElement = $field.find('input');

                var isSelect2 =  $inputElement.hasClass('select-field');

                if (isSelect2) {
                    $inputElement.select2('val', value).trigger('change');
                } else {
                    $inputElement.val(value).trigger('change');
                }
            },
            field.toLowerCase(),
            value,
            callback
        );
    });

    this.When(/^I save the (.+)$/, function (entity, callback) {
        this.browser
            .click('button[type="submit"]')
            .waitForComplete(callback);
    });

    this.Then(/^I press the "([^"]*)" button$/, function (name, callback) {
        var browser = this.browser;
        browser
            .execute(function (button) {
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
