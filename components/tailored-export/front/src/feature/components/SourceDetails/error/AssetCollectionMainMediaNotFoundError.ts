class AssetCollectionMainMediaNotFoundError extends Error {
  constructor(msg: string) {
    super(msg);

    Object.setPrototypeOf(this, AssetCollectionMainMediaNotFoundError.prototype);
  }
}

export {AssetCollectionMainMediaNotFoundError};
