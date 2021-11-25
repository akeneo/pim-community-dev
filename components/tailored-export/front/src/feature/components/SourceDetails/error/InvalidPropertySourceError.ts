class InvalidPropertySourceError extends Error {
  constructor(msg: string) {
    super(msg);

    Object.setPrototypeOf(this, InvalidPropertySourceError.prototype);
  }
}

export {InvalidPropertySourceError};
