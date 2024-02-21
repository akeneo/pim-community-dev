'use strict';

const fs = require('fs');

console.log(JSON.parse(fs.readFileSync('./package.json')).version);
