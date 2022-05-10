class InvalidPropertyTargetError extends Error {
  constructor(msg: string) {
    super(msg);

    Object.setPrototypeOf(this, InvalidPropertyTargetError.prototype);
  }
}

export {InvalidPropertyTargetError};
