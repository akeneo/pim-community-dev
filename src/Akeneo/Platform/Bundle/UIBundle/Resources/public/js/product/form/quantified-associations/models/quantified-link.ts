import {Identifier} from '.';

type QuantifiedLink = {
  identifier: Identifier;
  quantity: string;
};

const setQuantifiedLink = (collection: QuantifiedLink[], quantifiedLink: QuantifiedLink): QuantifiedLink[] =>
  collection.map(current => (current.identifier === quantifiedLink.identifier ? quantifiedLink : current));

export {QuantifiedLink, setQuantifiedLink};
