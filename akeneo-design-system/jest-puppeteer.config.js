module.exports = {
  launch: {
    dumpio: true,
    headless: true,
  },
  server: {
    command: 'yarn storybook:ci:start',
    port: 6006,
    launchTimeout: 30000
  },
}
