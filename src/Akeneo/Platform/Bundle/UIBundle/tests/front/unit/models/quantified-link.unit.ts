import {
  setQuantifiedLink,
  removeQuantifiedLink,
} from '../../../../Resources/public/js/product/form/quantified-associations/models/quantified-link';

const links = [
  {identifier: 'bag', quantity: 6},
  {identifier: 'braided-hat', quantity: 9},
];

describe('quantified link', () => {
  it('should update the provided quantified link among the provided collection', () => {
    expect(setQuantifiedLink(links, {identifier: 'bag', quantity: 60})).toEqual([
      {identifier: 'bag', quantity: 60},
      {identifier: 'braided-hat', quantity: 9},
    ]);
  });

  it('should remove the corresponding quantified link from the provided collection', () => {
    expect(removeQuantifiedLink(links, 'bag')).toEqual([{identifier: 'braided-hat', quantity: 9}]);
  });
});
