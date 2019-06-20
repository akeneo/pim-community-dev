import {ConcreteChannel, denormalizeChannel} from 'akeneoreferenceentity/domain/model/channel';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {denormalizeLocale} from 'akeneoreferenceentity/domain/model/locale';

describe('akeneo > reference entity > domain > model --- channel', () => {
  test('I can create a new channel from a normalized one', () => {
    const channel = denormalizeChannel({
      code: 'ecommerce',
      labels: {en_US: 'E-commerce'},
      locales: [
        {
          code: 'en_US',
          label: 'English (United States)',
          region: 'United States',
          language: 'English',
        },
      ],
    });
    expect(channel.code).toBe('ecommerce');
    expect(channel.getLabel('en_US')).toBe('E-commerce');
    expect(channel.getLabel('fr_FR')).toBe('[ecommerce]');
  });

  test('I cannot create a new channel with invalid parameters', () => {
    expect(() => {
      denormalizeChannel({labels: {}, locales: []});
    }).toThrow('Channel expects a string as code to be created');

    expect(() => {
      new ConcreteChannel('toto', {}, []);
    }).toThrow('Channel expects a LabelCollection as second argument');

    expect(() => {
      new ConcreteChannel('toto', createLabelCollection({}), [
        denormalizeLocale({
          code: 'en_US',
          label: 'English (United States)',
          region: 'United States',
          language: 'English',
        }),
        {},
      ]);
    }).toThrow('Channel expects a Locale collection as third argument');

    expect(() => {
      new ConcreteChannel('toto', createLabelCollection({}), [{}]);
    }).toThrow('Channel expects a Locale collection as third argument');
  });
});
