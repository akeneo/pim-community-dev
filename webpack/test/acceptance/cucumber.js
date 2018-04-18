const cucumber = require('cucumber');
const cwd = process.cwd();
const EventEmitter = require('events');

const cli = new cucumber.Cli({
    argv: process.argv,
    cwd,
    stdout: process.stdout
});


cli.run = async function() {
    const configuration = await this.getConfiguration();
    const supportCodeLibrary = this.getSupportCodeLibrary(configuration);
    const eventBroadcaster = new EventEmitter();

    const cleanup = await this.initializeFormatters({
        eventBroadcaster,
        formatOptions: configuration.formatOptions,
        formats: configuration.formats,
        supportCodeLibrary
    });

    const testCases = await cucumber.getTestCasesFromFilesystem({
        cwd: this.cwd,
        eventBroadcaster,
        featureDefaultLanguage: configuration.featureDefaultLanguage,
        featurePaths: configuration.featurePaths,
        order: configuration.order,
        pickleFilter: new cucumber.PickleFilter(configuration.pickleFilterOptions)
    });

    let success;

    const runtime = new cucumber.Runtime({
        eventBroadcaster,
        options: configuration.runtimeOptions,
        supportCodeLibrary,
        testCases
    });
    success = await runtime.start();

    await cleanup();

    return {
        shouldExitImmediately: configuration.shouldExitImmediately,
        success
    };
};

cli.run();
