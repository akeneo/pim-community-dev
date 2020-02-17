import limitFileUpload from 'akeneoassetmanager/application/asset-upload/utils/upload-limit';
import notify from 'akeneoassetmanager/tools/notify';

jest.mock('akeneoassetmanager/tools/notify', () => ({
  __esModule: true,
  default: jest.fn(),
}));

describe('akeneoassetmanager/application/asset-upload/utils/upload-limit.ts', () => {
  beforeEach(() => {
    notify.mockClear();
  });

  test('I can upload files up to a certain amount', async () => {
    const files = Array.from(Array(500).keys()).map(index => new File(['foo'], index + '.png', {type: 'image/png'}));

    const result = limitFileUpload(files, 0);

    expect(notify).not.toHaveBeenCalled();
    expect(result.length).toEqual(500);
  });

  test('I cannot upload more files than allowed', async () => {
    const files = Array.from(Array(501).keys()).map(index => new File(['foo'], index + '.png', {type: 'image/png'}));

    const result = limitFileUpload(files, 0);

    expect(notify).toHaveBeenCalled();
    expect(result.length).toEqual(500);
  });

  test('The lists of files are truncated when the limit is about to be reached', async () => {
    const files = Array.from(Array(300).keys()).map(index => new File(['foo'], index + '.png', {type: 'image/png'}));

    let result = limitFileUpload(files, 0);

    expect(notify).not.toHaveBeenCalled();
    expect(result.length).toEqual(300);

    result = limitFileUpload(files, result.length);

    expect(notify).toHaveBeenCalled();
    expect(result.length).toEqual(200);
  });

  test('The lists of files are emptied when the limit is reached', async () => {
    const files = Array.from(Array(500).keys()).map(index => new File(['foo'], index + '.png', {type: 'image/png'}));

    let result = limitFileUpload(files, 0);

    expect(notify).not.toHaveBeenCalled();
    expect(result.length).toEqual(500);

    result = limitFileUpload(files, result.length);

    expect(notify).toHaveBeenCalled();
    expect(result.length).toEqual(0);
  });
});
