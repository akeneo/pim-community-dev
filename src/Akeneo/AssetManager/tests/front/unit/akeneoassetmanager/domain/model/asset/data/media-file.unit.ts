import {
  isMediaFileData,
  areMediaFileDataEqual,
  getMediaFilePath,
  PLACEHOLDER_PATH,
} from 'akeneoassetmanager/domain/model/asset/data/media-file';

const mediaFileData = {
  filePath: 'coucou',
  originalFilename: 'coucou_org',
};

describe('akeneo > asset family > domain > model > asset > data --- media-file', () => {
  test('I can test if a MediaFileData is equal to another', () => {
    expect(areMediaFileDataEqual(mediaFileData, null)).toBe(false);
    expect(areMediaFileDataEqual(null, null)).toBe(true);
    expect(areMediaFileDataEqual(null, mediaFileData)).toBe(false);
    expect(areMediaFileDataEqual(mediaFileData, mediaFileData)).toBe(true);
  });

  test('I can test if something is a MediaFileData', () => {
    expect(isMediaFileData(mediaFileData)).toBe(true);
    expect(isMediaFileData(null)).toBe(true);
    expect(isMediaFileData({})).toBe(false);
  });

  test('I can get the media file path of a MediaFileData', () => {
    expect(getMediaFilePath(mediaFileData)).toEqual('coucou');
  });

  test('I should get a placeholder image path if the MediaFileData is empty', () => {
    expect(getMediaFilePath(null)).toEqual(PLACEHOLDER_PATH);
  });
});
