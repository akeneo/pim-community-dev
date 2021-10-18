const Mediator = {
  trigger: (event: string) => event,
  on: (event: string, _callback: () => void) => event,
};
module.exports = Mediator;
