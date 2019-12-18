import {
  mediaLinkDataFromString,
  mediaLinkDataStringValue,
  areMediaLinkDataEqual,
  isMediaLinkData,
} from 'akeneoassetmanager/domain/model/asset/data/media-link';

let mediaLinkData = 'https://my-link.com';
describe('akeneo > asset family > domain > model > asset > data --- media-link', () => {
  test('I can create a new MediaLinkData with a string', () => {
    expect(mediaLinkDataFromString(mediaLinkData)).toEqual(mediaLinkData);
    expect(mediaLinkDataFromString('')).toEqual(null);
  });

  test('I can get the string value of a MediaLinkData', () => {
    expect(mediaLinkDataStringValue(mediaLinkData)).toEqual(mediaLinkData);
    expect(mediaLinkDataStringValue(null)).toEqual('');
  });

  test('I can test if a MediaLinkData is equal to another', () => {
    expect(areMediaLinkDataEqual(mediaLinkData, null)).toBe(false);
    expect(areMediaLinkDataEqual(null, null)).toBe(true);
    expect(areMediaLinkDataEqual(null, mediaLinkData)).toBe(false);
    expect(areMediaLinkDataEqual(mediaLinkData, mediaLinkData)).toBe(true);
  });

  test('I can test if something is a MediaLinkData', () => {
    expect(isMediaLinkData(mediaLinkData)).toBe(true);
    expect(isMediaLinkData(null)).toBe(true);
    expect(isMediaLinkData({})).toBe(false);
  });
});
