import {uploadFile, shouldCreateThumbnailFromFile} from 'akeneoassetmanager/application/asset-upload/utils/file';
import {createFakeAssetFamily, createFakeLine} from '../tools';

const uploader = jest.fn((file, updateProgress) => {
  updateProgress(0);
  updateProgress(1);

  return new Promise(resolve => {
    resolve({
      originalFilename: file.name,
      filePath: '/public/' + file.name,
    });
  });
});

const createFakeLineFromFilename = (filename: string) => {
  const valuePerLocale = false;
  const valuePerChannel = false;
  const assetFamily = createFakeAssetFamily(valuePerLocale, valuePerChannel);
  const channels: Channel[] = [];
  const locales: Locale[] = [];

  return createFakeLine(filename, assetFamily, channels, locales);
};

describe('akeneoassetmanager/application/asset-upload/utils/file.ts -> uploadFile', () => {
  test('I cannot upload an undefined file', async () => {
    const uploadedFile = await uploadFile(uploader);

    expect(uploadedFile).toBe(null);
  });

  test('I can upload a file and follow the progress', async () => {
    const line = createFakeLineFromFilename('foo.png');
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const handleProgress = jest.fn();

    const uploadedFile = await uploadFile(uploader, file, line, handleProgress);

    expect(handleProgress).toHaveBeenCalledWith(line, 0);
    expect(handleProgress).toHaveBeenCalledWith(line, 1);

    expect(uploadedFile).toEqual({
      originalFilename: 'foo.png',
      filePath: '/public/foo.png',
    });
  });

  test('I get an error if the upload is refused', async () => {
    uploader.mockImplementationOnce(() => Promise.reject());

    const handleCatch = jest.fn();

    const line = createFakeLineFromFilename('foo.png');
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const handleProgress = jest.fn();

    await uploadFile(uploader, file, line, handleProgress).catch(handleCatch);

    expect(handleCatch).toHaveBeenCalled();
  });

  test('I get an error if the upload failed', async () => {
    uploader.mockImplementationOnce(() => {
      throw new Error();
    });

    const handleCatch = jest.fn();

    const line = createFakeLineFromFilename('foo.png');
    const file = new File(['foo'], 'foo.png', {type: 'image/png'});
    const handleProgress = jest.fn();

    await uploadFile(uploader, file, line, handleProgress).catch(handleCatch);

    expect(handleCatch).toHaveBeenCalled();
  });
});

describe('akeneoassetmanager/application/asset-upload/utils/file.ts -> shouldCreateThumbnailFromFile', () => {
  test('I allow creation of thumbnail for images', () => {
    expect(shouldCreateThumbnailFromFile(new File(['foo'], 'foo.png', {type: 'image/png'}))).toEqual(true);
    expect(shouldCreateThumbnailFromFile(new File(['foo'], 'foo.jpg', {type: 'image/jpeg'}))).toEqual(true);
  });

  test('I allow creation of thumbnail for svg', () => {
    expect(shouldCreateThumbnailFromFile(new File(['foo'], 'foo.svg', {type: 'image/svg+xml'}))).toEqual(true);
  });

  test('I disallow creation of thumbnail for other mimetypes', () => {
    expect(shouldCreateThumbnailFromFile(new File(['foo'], 'foo.pdf', {type: 'application/pdf'}))).toEqual(false);
  });
});
