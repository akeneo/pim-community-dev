class InvalidAssociationTypeSourceError extends Error {
  constructor(msg: string) {
    super(msg);

    Object.setPrototypeOf(this, InvalidAssociationTypeSourceError.prototype);
  }
}

export {InvalidAssociationTypeSourceError};
