module.exports = {
  launch: {
    dumpio: true,
    headless: true,
  },
  server: {
    command: 'yarn http-server storybook-static -p 6006',
    launchTimeout: 90000
  },
}
