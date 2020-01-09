import {uploadFile} from 'akeneoassetmanager/application/asset-upload/utils/file';

jest.mock('akeneoassetmanager/infrastructure/uploader/image', () => ({
  upload: jest.fn().mockImplementation((file, updateProgress) => {
    if (undefined !== file && file.file === 'invalid_data') {
      throw Error('an error occured');
    }
    setTimeout(() => updateProgress(0.5), 10);
    setTimeout(() => updateProgress(1), 30);

    return new Promise(resolve => {
      setTimeout(() => resolve({originalFilename: 'nice'}), 50);
    });
  }),
}));

describe('akeneoassetmanager/application/asset-upload/utils/file.ts -> uploadFile', () => {
  test('I cannot upload an undefined file', async () => {
    const uploadedFile = await uploadFile();

    expect(uploadedFile).toBe(null);
  });

  test('I can upload a file an follow the progress', async () => {
    let progress = 0;
    const uploadedFile = await uploadFile({file: 'data'}, {code: '12'}, (line, currentProgress) => {
      progress = currentProgress;
    });

    expect(progress).toBe(1);
    expect(uploadedFile).toEqual({originalFilename: 'nice'});
  });

  test('I get an error if the upload failed', async () => {
    let errorCaught = false;
    await uploadFile({file: 'invalid_data'}, {code: '12'}, (line, currentProgress) => {}).catch(error => {
      errorCaught = true;
    });

    expect(errorCaught).toBe(true);
  });
});
