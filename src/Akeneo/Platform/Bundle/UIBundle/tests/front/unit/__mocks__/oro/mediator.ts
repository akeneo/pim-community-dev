const Mediator = {
  on: (event: string, _callback: () => void) => event,
};
module.exports = Mediator;
