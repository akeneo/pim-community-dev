import limitFileUpload from 'akeneoassetmanager/application/asset-upload/utils/upload-limit';

describe('akeneoassetmanager/application/asset-upload/utils/upload-limit.ts', () => {
  test('I can upload files up to a certain amount', async () => {
    const notify = jest.fn();
    const translate = jest.fn();
    const files = Array.from(Array(500).keys()).map(index => new File(['foo'], index + '.png', {type: 'image/png'}));

    const result = limitFileUpload(notify, translate, files, 0);

    expect(notify).not.toHaveBeenCalled();
    expect(result.length).toEqual(500);
  });

  test('I cannot upload more files than allowed', async () => {
    const notify = jest.fn();
    const translate = jest.fn();
    const files = Array.from(Array(501).keys()).map(index => new File(['foo'], index + '.png', {type: 'image/png'}));

    const result = limitFileUpload(notify, translate, files, 0);

    expect(notify).toHaveBeenCalled();
    expect(result.length).toEqual(500);
  });

  test('The lists of files are truncated when the limit is about to be reached', async () => {
    const notify = jest.fn();
    const translate = jest.fn();
    const files = Array.from(Array(300).keys()).map(index => new File(['foo'], index + '.png', {type: 'image/png'}));

    let result = limitFileUpload(notify, translate, files, 0);

    expect(notify).not.toHaveBeenCalled();
    expect(result.length).toEqual(300);

    result = limitFileUpload(notify, translate, files, result.length);

    expect(notify).toHaveBeenCalled();
    expect(result.length).toEqual(200);
  });

  test('The lists of files are emptied when the limit is reached', async () => {
    const notify = jest.fn();
    const translate = jest.fn();
    const files = Array.from(Array(500).keys()).map(index => new File(['foo'], index + '.png', {type: 'image/png'}));

    let result = limitFileUpload(notify, translate, files, 0);

    expect(notify).not.toHaveBeenCalled();
    expect(result.length).toEqual(500);

    result = limitFileUpload(notify, translate, files, result.length);

    expect(notify).toHaveBeenCalled();
    expect(result.length).toEqual(0);
  });
});
