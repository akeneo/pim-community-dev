/* jshint node:true */
'use strict';

var browser = null;

var browserHook = function () {
    this.Around(function (runScenario) {
        browser = this.browser;

        runScenario(function (callback) {
            this.browser.end();

            callback();
        });
    });

    this.After(function (scenario, callback) {
        if (!browser || !scenario.isFailed()) {
            return callback();
        }
        browser.saveScreenshot(function (err, buffer) {
            scenario.attach(buffer.toString('binary'), 'image/png');
            browser.end();
            callback();
        });
    });
};

module.exports = browserHook;
