const process = require('process');
const { exec, execSync } = require('child_process');
const rootDir = process.cwd();
const path = require('path');
const fs = require('fs');

const read = require('read-pkg').sync;
const diff = require('dep-diff');
require('colors');

const { ChangedDep, AddedDep, RemovedDep } = diff;

/**
 * Get the source directory to sync package.json files from
 * @return {String} The source directory extracted from arguments
 */
const getSourceDir = () => {
    const args = process.argv || [];
    const argname = '--source=';
    const source = args.find(arg => arg.startsWith(argname));

    if (typeof source === undefined || typeof source !== 'string') {
        return console.log('You must include the --source=<directory> argument');
    }

    return source.split(argname)[1];
};

/**
 * Get the diff between two package.json files (only changed or updated dependencies)
 * @param  {String} source The source directory
 * @return {Array}        An array of packages that are different
 */
const getPackageDiffs = (source) => {
    if (!fs.existsSync(path.join(source, 'package.json'))) {
        console.log(`No package.json found in ${source} - Make sure you run composer update`.yellow);
        return [];
    }

    const sourceJSON = read(source);
    const currentJSON = read(rootDir);
    const diffList = diff(currentJSON.dependencies, sourceJSON.dependencies);

    return diffList.filter(dep => !(dep instanceof RemovedDep));
};

/**
 * Logs the updated and added dependencies
 * @param  {Array} diffs An array of packages that have changed
 */
const reportDiffs = (diffs) => {
    console.log('\nYou have some npm dependencies that are out of date with the PIM:'.yellow);

    const groups = groupByType(diffs);

    for (let group in groups) {
        console.log(`\n    ${group.yellow}`);
        if (group === 'Updated') logUpdated(groups[group]);
        if (group === 'Added') logAdded(groups[group]);
    }

    console.log('\n');
};

/**
 * Return a string for the different dependency types
 * @param  {Object} diff An object containing package diff information
 * @return {String}
 */
const getType = (diff) => {
    if (diff instanceof AddedDep) return 'Added';
    if (diff instanceof ChangedDep) return 'Updated';
};

/**
 * Groups the diffed dependencies by type
 * @param  {Array} diffs
 * @return {Object}
 */
const groupByType = (diffs) => {
    return diffs.reduce(function(diff, prev) {
        (diff[getType(prev)] = diff[getType(prev)] || []).push(prev);

        return diff;
    }, {});
};

/**
 * Logs the updated version for a package
 * Example:
 *     Updated
 *      - JSON2@0.1.0 -> 0.1.1
 * @param  {Array} diffs [description]
 */
const logUpdated = (diffs) => {
    diffs.forEach(diff => {
        console.log(`    - ${diff.name}@${diff.previous.red} -> ${diff.version.green}`);
    });
};

/**
 * Logs the newly added packages
 * Example
 *     Added
 *     - test@1.2.3
 * @param  {Array} diffs [description]
 */
const logAdded = (diffs) => {
    diffs.forEach(diff => {
        console.log(`    - ${diff.name}@${diff.version}`.green);
    });
};

/**
 * Generates a yarn command to install missing and updated dependencies
 * @param  {Array} dependencies
 */
const generateInstallString = (dependencies) => {
    const packageString = dependencies.reduce((dep, next) => {
        return dep += `${next.name}@${next.version} `;
    }, '');

    const command = `yarn add ${packageString}`;

    console.log('Automatically syncing your dependencies...'.yellow);
    console.log(`\n  Running: ${command}\n`.green);

    return command;
};

try {
    const sourceDir = getSourceDir();
    const diffs = getPackageDiffs(sourceDir);

    if (diffs.length > 0) {
        reportDiffs(diffs);
        const command = generateInstallString(diffs);
        execSync(command);
    }
} catch (e) {
    console.log('Error checking dependencies'.yellow, e.message);
}
