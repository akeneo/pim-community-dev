import {
  denormalizeChannelReference,
  channelReferenceStringValue,
  channelReferenceAreEqual,
  channelReferenceIsEmpty,
} from 'akeneoassetmanager/domain/model/channel-reference';

test('I can create a new channel reference with a string value', () => {
  expect(channelReferenceStringValue(denormalizeChannelReference('ecommerce'))).toBe('ecommerce');
});

test('I can create a new channel reference with a null value', () => {
  expect(channelReferenceStringValue(denormalizeChannelReference(null))).toBe('');
});

test('I cannot create a new channel reference with a value other than a string or null', () => {
  expect(() => {
    denormalizeChannelReference(12);
  }).toThrow('A channel reference should be a string or null');
});

test('I can compare two channel references', () => {
  expect(
    channelReferenceAreEqual(denormalizeChannelReference('ecommerce'), denormalizeChannelReference('mobile'))
  ).toBe(false);
  expect(
    channelReferenceAreEqual(denormalizeChannelReference('ecommerce'), denormalizeChannelReference('ecommerce'))
  ).toBe(true);
  expect(channelReferenceAreEqual(denormalizeChannelReference(null), denormalizeChannelReference(null))).toBe(true);
  expect(channelReferenceAreEqual(denormalizeChannelReference('ecommerce'), denormalizeChannelReference(null))).toBe(
    false
  );
});

test('I can know if a channel reference is empty', () => {
  expect(channelReferenceIsEmpty(denormalizeChannelReference('ecommerce'))).toBe(false);
  expect(channelReferenceIsEmpty(denormalizeChannelReference(null))).toBe(true);
});

test('I can get the string value of a channel reference', () => {
  expect(channelReferenceStringValue(denormalizeChannelReference('ecommerce'))).toBe('ecommerce');
  expect(channelReferenceStringValue(denormalizeChannelReference(null))).toBe('');
});
