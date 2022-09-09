import {Attribute} from './Attribute';

const isAttributeCollection = (collection: Attribute[]): collection is Attribute[] => {
  if (undefined === collection || typeof collection !== 'object') {
    return false;
  }

  // TODO this does not check if element is of Attribute type
  return collection.every((attribute: Attribute) => typeof attribute === 'object');
};

export {isAttributeCollection};
