import {hidrator} from 'akeneoenrichedentity/application/hidrator/enriched-entity';

describe('akeneo > enriched entity > application > hidrator --- enriched entity', () => {
  test('I can hidrate a new enriched entity', () => {
    const hidrate = hidrator(
      (identifier, labelCollection) => {
        expect(identifier).toEqual('designer');
        expect(labelCollection).toEqual({en_US: 'Designer'});
      },
      identifier => {
        expect(identifier).toEqual('designer');

        return identifier;
      },
      labelCollection => {
        expect(labelCollection).toEqual({en_US: 'Designer'});

        return labelCollection;
      }
    );

    expect(hidrate({identifier: 'designer', labels: {en_US: 'Designer'}}));
  });
});
