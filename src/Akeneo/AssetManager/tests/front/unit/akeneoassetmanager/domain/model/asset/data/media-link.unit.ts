import {
  mediaLinkDataFromString,
  mediaLinkDataStringValue,
  areMediaLinkDataEqual,
  isMediaLinkData,
  getMediaLinkUrl,
  getYouTubeWatchUrl,
  getYouTubeEmbedUrl,
  getVimeoWatchUrl,
  getVimeoEmbedUrl,
} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';

const mediaLinkAttribute = {
  identifier: 'url',
  asset_family_identifier: 'designer',
  code: 'url',
  labels: {en_US: 'Url'},
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  suffix: '.jpg',
  prefix: 'https://',
  media_type: 'image',
};
const mediaLinkData = 'my-link';

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

  test('I can get the full URL of a media link', () => {
    expect(getMediaLinkUrl(mediaLinkData, mediaLinkAttribute)).toEqual('https://my-link.jpg');
    expect(getMediaLinkUrl(mediaLinkData, {...mediaLinkAttribute, media_type: 'youtube'})).toEqual(
      'https://youtube.com/watch?v=' + mediaLinkData
    );
    expect(getMediaLinkUrl(mediaLinkData, {...mediaLinkAttribute, media_type: 'vimeo'})).toEqual(
      'https://vimeo.com/' + mediaLinkData
    );
    expect(() => getMediaLinkUrl(mediaLinkData, {...mediaLinkAttribute, type: 'text'})).toThrow();
  });

  test('I can get the youtube watch url of a media link data', () => {
    expect(getYouTubeWatchUrl(mediaLinkData)).toEqual('https://youtube.com/watch?v=' + mediaLinkData);
  });

  test('I can get the youtube embed url of a media link data', () => {
    expect(getYouTubeEmbedUrl(mediaLinkData)).toEqual('https://youtube.com/embed/' + mediaLinkData);
  });

  test('I can get the vimeo watch url of a media link data', () => {
    expect(getVimeoWatchUrl(mediaLinkData)).toEqual('https://vimeo.com/' + mediaLinkData);
  });

  test('I can get the vimeo embed url of a media link data', () => {
    expect(getVimeoEmbedUrl(mediaLinkData)).toEqual('https://player.vimeo.com/video/' + mediaLinkData);
  });
});
