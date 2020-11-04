const {execSync} = require('child_process');

/**
 * This command is here to check if you modified something outside your domain of responsibility.
 *
 * The goal is to list the modified files on the PR and check agains a white list of files
 * that can be edited without impacting the rest of the application
 */

//The origin branch to compare to
const originBranch = process.argv[2];
if (undefined === originBranch) {
  console.error('You need to pass an origin branch to this command as first argument');

  return;
}

//The paths in the white list
const allowedPathsCSV = process.argv[3];
if (undefined === allowedPathsCSV) {
  console.error('You need to pass a allowed paths to check to this command as second argument');

  return;
}

const allowedPaths = allowedPathsCSV.split(',');
execSync(`git remote set-branches --add origin ${originBranch} 2>/dev/null && git fetch 2>/dev/null`);
const rawModifiedFiles = execSync(`git diff --name-only origin/${originBranch}`);
const modifiedFiles = rawModifiedFiles
  .toString('utf8')
  .split('\n')
  .filter(file => '' !== file);

/**
 * This method check the given path against the list of allowed paths
 */
const isModificationAllowed = allowedPaths => pathToCheck => {
  return !allowedPaths.some(allowedPath => pathToCheck.startsWith(allowedPath));
};

//This variable will contain the number of files that could lead to breaking things in the app
const violationFiles = modifiedFiles.filter(isModificationAllowed(allowedPaths));

if (violationFiles.length) {
  console.log(`############################
######### Error! ###########
############################

It seems that your PR is modifying a file outside the white list of authorised paths.
Please run a full CI on this PR.

The following files are outside the white list of files that can be modified:
 - ${violationFiles.join('\n - ')}`);
}

process.exit(violationFiles.length === 0 ? 0 : 1);
