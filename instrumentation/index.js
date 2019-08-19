// requires all tests in `project/test/src/components/**/index.js`
const tests = require.context('./src', true, /index\.{js,ts,tsx}$/);

console.log(tests.keys().forEach(tests));
