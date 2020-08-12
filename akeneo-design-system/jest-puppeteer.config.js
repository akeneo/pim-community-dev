module.exports = {
  launch: {
    dumpio: true,
    headless: false,
  },
  server: {
    command: 'yarn storybook:ci',
    port: 6006,
    launchTimeout: 30000
  },
}
