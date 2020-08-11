'use strict';

const {execSync} = require('child_process');
const fs = require('fs');

const filePath = process.argv[2];

if (typeof filePath === 'undefined') {
  console.log(`usage: ${process.argv[0]} ${process.argv[1]} path_to_webhook_event.json`);

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

const levelToBump = githubEvent.commits.reduce((currentBumpLevel, commit) => {
  const bumpLevel = getCommitMessageBumpLevel(commit.message);

  return bumpLevel > currentBumpLevel ? bumpLevel : currentBumpLevel;
}, BumpLevelEnum.Patch);

execSync(`npm --no-git-tag-version version ${getBumpNameFromBumpLevel(levelToBump)}`);

console.log(JSON.parse(fs.readFileSync('./package.json')).version);
