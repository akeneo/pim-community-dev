import {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';

const mediaFileData = {
  filePath: 'path/to/file',
  originalFilename: 'niceName',
};
const mediaLinkData = 'linktofile';
const otherData = {other: 'other'};

describe('akeneo > asset family > domain > model > asset --- data', () => {
  test('I can get the meaningful data of a given MediaData', () => {
    expect(getMediaData(mediaFileData)).toEqual('path/to/file');
    expect(getMediaData(mediaLinkData)).toEqual('linktofile');
    expect(getMediaData(null)).toEqual('');
    expect(getMediaData(otherData)).toEqual('');
  });
});
