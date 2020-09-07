
'use strict';

const {execSync} = require('child_process');
const fs = require('fs');

const filePath = process.argv[2];
const externalPackageJson = process.argv[3];

if (typeof filePath === 'undefined') {
    console.log(`usage: ${process.argv[0]} ${process.argv[1]} path_to_webhook_event.json external/package.json`);

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

const messages = (new String(execSync(`git rev-list --ancestry-path ${githubEvent.before}...${githubEvent.after} | xargs -n1 git log -n 1 --pretty=format:%s`))).split('\n');
console.log(messages);
console.log(new String(messages));
const levelToBump = messages.reduce((currentBumpLevel, commit) => {
    const bumpLevel = getCommitMessageBumpLevel(commit);

    return bumpLevel > currentBumpLevel ? bumpLevel : currentBumpLevel;
}, BumpLevelEnum.Patch);

const externalVersion = JSON.parse(fs.readFileSync(externalPackageJson)).version

execSync(`npm --no-git-tag-version version ${externalVersion}`);
execSync(`npm --no-git-tag-version version ${getBumpNameFromBumpLevel(levelToBump)}`);
