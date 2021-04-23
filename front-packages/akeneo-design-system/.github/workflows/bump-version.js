
'use strict';

const {execSync} = require('child_process');
const fs = require('fs');

const filePath = process.argv[2];
const externalPackageJson = process.argv[3];
const commitMessagesFilepath = process.argv[4];

if (typeof filePath === 'undefined') {
    console.log(`usage: ${process.argv[0]} ${process.argv[1]} path_to_webhook_event.json external/package.json commit-messages.txt`);

    return;
}

// Let's define an enum for our bump levels
const BumpLevelEnum = {
    Patch: 0,
    Minor: 1,
    Major: 2,
}
Object.freeze(BumpLevelEnum);

// Extract the bump level to extract from the commit message
const getCommitMessageBumpLevel = (message) => {
    if (message.includes('#major')) return BumpLevelEnum.Major;
    if (message.includes('#minor')) return BumpLevelEnum.Minor;

    return BumpLevelEnum.Patch;
}

const getBumpNameFromBumpLevel = (bumpLevel) => {
    switch (bumpLevel) {
        case BumpLevelEnum.Patch:
            return 'patch';
        case BumpLevelEnum.Minor:
            return 'minor';
        case BumpLevelEnum.Major:
            return 'major';
        default:
            throw Error('Invalid bump level');
    }
}

const rawdata = fs.readFileSync(filePath);
const githubEvent = JSON.parse(rawdata);

const lastCommits = (new String(execSync(`git rev-list ${githubEvent.before}..HEAD`))).split('\n');
const messages = lastCommits.map(commit => new String(execSync(`git log -n 1 --pretty=format:%s ${commit}`)));
fs.writeFileSync(commitMessagesFilepath, `Extract from https://github.com/${githubEvent.repository.full_name}/commit/${githubEvent.after}\nWith commits:\n${messages.join('\n')}`);

const levelToBump = messages.reduce((currentBumpLevel, commit) => {
    const bumpLevel = getCommitMessageBumpLevel(commit);

    return bumpLevel > currentBumpLevel ? bumpLevel : currentBumpLevel;
}, BumpLevelEnum.Patch);

const externalVersion = JSON.parse(fs.readFileSync(externalPackageJson)).version

execSync(`npm --no-git-tag-version version ${externalVersion}`);
execSync(`npm --no-git-tag-version version ${getBumpNameFromBumpLevel(levelToBump)}`);
