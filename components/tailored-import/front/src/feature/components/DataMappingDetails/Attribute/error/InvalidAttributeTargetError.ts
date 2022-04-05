class InvalidAttributeTargetError extends Error {
  constructor(msg: string) {
    super(msg);

    Object.setPrototypeOf(this, InvalidAttributeTargetError.prototype);
  }
}

export {InvalidAttributeTargetError};
