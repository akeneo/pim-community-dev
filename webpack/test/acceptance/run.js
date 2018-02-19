var reporter = require('cucumber-html-reporter');
const argv = require('yargs').argv
const cucumber = require('cucumber');
const VError = require('verror');

function exitWithError(error) {
  console.error(VError.fullStack(error)) // eslint-disable-line no-console
  process.exit(1)
}

const reportParameters = true === argv.report ? ['-f', 'json:coverage/acceptance-js.json'] : [];
const featureParameters = 1 === argv._.length ? [argv._[0]] : ['./src/Pim/Bundle/FrontBundle/Resources/test/acceptance/features'];
const worldParameters = ['--world-parameters', JSON.stringify({
  debug: true === argv.debug,
  random: argv.random || true,
  maxLatency: argv.maxLatency || 1000
})];

const defaultParameters = [
  process.argv[0],
  process.argv[1],
  '-r',
  './src/Pim/Bundle/FrontBundle/Resources/test/acceptance/cucumber',
  '-f',
  'progress'
];

const parameters = [...defaultParameters, ...worldParameters, ...featureParameters, ...reportParameters]

const run = async function () {
  const cwd = process.cwd()
  const cli = new cucumber.Cli({
    argv: parameters,
    cwd,
    stdout: process.stdout,
  })

  let result
  try {
    result = await cli.run()
  } catch (error) {
    exitWithError(error)
  }

  const exitCode = result.success ? 0 : 1
  if (result.shouldExitImmediately) {
    process.exit(exitCode)
  } else {
    process.exitCode = exitCode
  }
}

run();

if (true === argv.report) {
  var options = {
      theme: 'bootstrap',
      jsonFile: 'coverage/acceptance-js.json',
      output: 'coverage/acceptance/cucumber_report.html',
      reportSuiteAsScenarios: true,
      launchReport: true
  };

  reporter.generate(options);
}

