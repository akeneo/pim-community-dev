var reporter = require('cucumber-html-reporter');
const argv = require('yargs').argv
const VError = require('verror');
const { exec, execSync } = require('child_process');

module.exports = function(cucumber, requirePaths) {
  function exitWithError(error) {
  console.error(VError.fullStack(error)) // eslint-disable-line no-console
  process.exit(1)
}

const reportParameters = true === argv.report ? ['-f', 'json:coverage/acceptance-js.json'] : [];
const featureParameters = 1 === argv._.length ? [argv._[0]] : ['./tests/front/acceptance/features'];
const worldParameters = ['--world-parameters', JSON.stringify({
  debug: true === argv.debug,
  random: argv.random || true,
  maxLatency: argv.maxLatency || 1000
})];

console.log(process.env.NODE_PATH);

const defaultParameters = [
  process.argv[0],
  process.argv[1],
  '-f',
  'progress'
];

const requireParams = requirePaths.reduce((prev, current) => [...prev, '-r', current], []);

const parameters = [...defaultParameters, ...requireParams, ...worldParameters, ...featureParameters, ...reportParameters]

const run = async function () {
  const cwd = process.cwd()
  console.log(parameters.join(' '))
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
}

