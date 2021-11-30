class InvalidAttributeSourceError extends Error {
  constructor(msg: string) {
    super(msg);

    Object.setPrototypeOf(this, InvalidAttributeSourceError.prototype);
  }
}

export {InvalidAttributeSourceError};
