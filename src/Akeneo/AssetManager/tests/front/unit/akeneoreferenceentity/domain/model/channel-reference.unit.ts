import {createChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';

describe('akeneo > reference entity > domain > model --- channel reference', () => {
  test('I can create a new channel reference with a string value', () => {
    expect(createChannelReference('ecommerce').stringValue()).toBe('ecommerce');
  });

  test('I can create a new channel reference with a null value', () => {
    expect(createChannelReference(null).stringValue()).toBe('');
  });

  test('I cannot create a new channel reference with a value other than a string or null', () => {
    expect(() => {
      createChannelReference(12);
    }).toThrow('ChannelReference expects a string or null as parameter to be created');
  });

  test('I can compare two channel references', () => {
    expect(createChannelReference('ecommerce').equals(createChannelReference('mobile'))).toBe(false);
    expect(createChannelReference('ecommerce').equals(createChannelReference('ecommerce'))).toBe(true);
    expect(createChannelReference(null).equals(createChannelReference(null))).toBe(true);
    expect(createChannelReference('ecommerce').equals(createChannelReference(null))).toBe(false);
  });

  test('I can know if a channel reference is empty', () => {
    expect(createChannelReference('ecommerce').isEmpty()).toBe(false);
    expect(createChannelReference(null).isEmpty()).toBe(true);
  });

  test('I can normalize a channel reference', () => {
    expect(createChannelReference('ecommerce').normalize()).toBe('ecommerce');
    expect(createChannelReference(null).normalize()).toBe(null);
  });

  test('I can get the string value of a channel reference', () => {
    expect(createChannelReference('ecommerce').stringValue()).toBe('ecommerce');
    expect(createChannelReference(null).stringValue()).toBe('');
  });
});
