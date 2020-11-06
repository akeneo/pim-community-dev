module.exports = {
  launch: {
    dumpio: true,
    headless: false,
  },
  server: {
    command: 'yarn http-server storybook-static -p 6006',
    launchTimeout: 90000
  },
}
