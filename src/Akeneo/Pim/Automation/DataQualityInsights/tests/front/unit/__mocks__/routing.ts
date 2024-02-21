const Routing = jest.fn();
// @ts-ignore
Routing.generate = jest.fn().mockImplementation((path: string) => {
  return path;
});

module.exports = Routing;
