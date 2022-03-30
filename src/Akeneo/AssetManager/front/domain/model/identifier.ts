import {isString} from 'akeneoassetmanager/domain/model/utils';

type Identifier = string;

export default Identifier;

export const denormalizeIdentifier = (identifier: any): Identifier => {
  if (!isIdentifier(identifier)) throw new Error('Identifier expects a string as parameter to be created');

  return identifier;
};

export const identifiersAreEqual = (first: Identifier, second: Identifier) =>
  first.toLowerCase() === second.toLowerCase();
export const identifierStringValue = (identifier: Identifier) => identifier;
export const isIdentifier = isString;
export const isEmptyIdentifier = (identifier: Identifier) => '' === identifier;
