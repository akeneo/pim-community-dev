/* jshint node:true */
'use strict';

var YAML = require('yamljs');
var webdriverio = require('webdriverio');
var fs = require('fs');
var url = require('url');
var path = require('path');
var exec = require('child_process').exec;

var WorldConstructor = function WorldConstructor(callback) {
    var config = YAML.parse(fs.readFileSync(path.resolve(__dirname, './../../../../behat.yml')).toString());

    var world = {
        baseUrl: config.default.context.parameters.base_url,
        browser: null,
        visit: function (link, callback) {
            return this.browser.url(url.resolve(this.baseUrl, link), callback);
        },
        listToArray: function (list) {
            if (list) {
                return list.replace(' and ', ', ').split(', ');
            }
            return [];
        },
        logLevel: null
    };

    var browserOptions = {
        port: 4444,
        // Firefox
        desiredCapabilities: {
            browserName: 'firefox'
        },
        // Phantom
        // desiredCapabilities: {
        //     browserName: 'phantomjs',
        //     'phantomjs.binary.path': require('phantomjs').path
        // },
        logLevel: world.logLevel
    };

    world.browser = webdriverio.remote(browserOptions).init();

    world.browser.addCommand('waitForComplete', function (cb) {
        var active = 3;

        var checkStatus = function () {
            world.browser.execute('return $.active;', function (err, result) {
                active = active - (1 - result.value);
                if (active) {
                    setTimeout(checkStatus, 50);
                } else {
                    cb();
                }
            });
        };

        checkStatus();
    });

    world.executeBehat = function (step, table, callback) {
        var content = '';
        if (!/^a "([^"]*)" catalog configuration$/.test(step)) {
            content += '@keepdb\n';
        }
        if (2 === arguments.length) {
            callback = table;
            table = null;
        }
        content += 'Feature:\nScenario:\nGiven ' + step;

        if (table) {
            content += ':\n';
            table.raw().forEach(function (row) {
                row.forEach(function (cell) {
                    content += '|' + cell;
                });
                content += '|\n';
            });
        }

        var featureFilePath = path.resolve(__dirname, './../cucumber.feature');
        fs.writeFileSync(featureFilePath, content);
        var process = exec('bin/behat features/Context/cucumber/cucumber.feature');

        if ('verbose' === world.logLevel) {
            process.stdout.on('data', function (data) {
                console.log('stdout: ' + data);
            });
        }

        process.on('close', function (code) {
            if (0 !== code) {
                throw new Error('Process exited with status:' + code);
            }
            fs.unlinkSync(featureFilePath);
            callback();
        });
    };

    callback(world);
};

exports.World = WorldConstructor;
