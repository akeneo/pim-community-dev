var nodemon = require('nodemon');
const manifest = require('./manifest.json');

nodemon({
    watch: manifest.extensionPaths,
    exec: 'yarn run webpack-dev',
    ext: 'js yml'
});

nodemon.on('start', function () {
  console.log('App has started');
}).on('quit', function () {
  console.log('App has quit');
  process.exit();
}).on('restart', function (files) {
  console.log('App restarted due to: ', files);
});