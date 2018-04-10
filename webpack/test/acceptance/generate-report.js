var reporter = require('cucumber-html-reporter');

var options = {
    theme: 'bootstrap',
    jsonFile: 'web/test_dist/acceptance-js.json',
    output: 'coverage/acceptance/cucumber_report.html',
    reportSuiteAsScenarios: true,
    launchReport: true
};

reporter.generate(options);
