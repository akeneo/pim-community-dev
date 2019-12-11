import {create, denormalize} from 'akeneoassetmanager/domain/model/asset/data/media-link';

let mediaLinkData = 'https://my-link.com';
describe('akeneo > asset family > domain > model > asset > data --- file', () => {

  test('I can create a new MediaLinkData with a string', () => {
    expect(create(mediaLinkData).normalize()).toEqual(mediaLinkData);
  });

  test('I cannot create a medua link data with a non-string argument', () => {
    expect(
      () => {create(123)}
      ).toThrowError('MediaLinkData expects a string as parameter to be created');
  });

  test('I can normalize a media link data', () => {
    expect(denormalize(mediaLinkData).normalize()).toEqual(mediaLinkData);
  });

  test('I can get the string value of a media link data', () => {
    expect(denormalize(mediaLinkData).stringValue()).toEqual(mediaLinkData);
  });

  test('I can test if a file is empty', () => {
    expect(denormalize(mediaLinkData).isEmpty()).toEqual(
      false
    );
    expect(denormalize(null).isEmpty()).toEqual(true);
  });

  test('I can test if a file is equal to another', () => {
    expect(create(mediaLinkData).equals(create(mediaLinkData))).toBe(true);
  });
});
