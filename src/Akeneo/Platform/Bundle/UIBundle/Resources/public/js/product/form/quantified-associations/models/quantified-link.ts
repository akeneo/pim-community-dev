import {Identifier} from '.';

type QuantifiedLink = {
  identifier: Identifier;
  quantity: number;
};

const setQuantifiedLink = (collection: QuantifiedLink[], quantifiedLink: QuantifiedLink): QuantifiedLink[] =>
  collection.map(current => (current.identifier === quantifiedLink.identifier ? quantifiedLink : current));

const removeQuantifiedLink = (collection: QuantifiedLink[], identifier: Identifier): QuantifiedLink[] =>
  collection.filter(current => current.identifier !== identifier);

export {QuantifiedLink, setQuantifiedLink, removeQuantifiedLink};
