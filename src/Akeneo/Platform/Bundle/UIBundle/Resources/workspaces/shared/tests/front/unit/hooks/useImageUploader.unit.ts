import {useImageUploader} from '../../../../src/hooks/useImageUploader';
import {renderHookWithProviders} from '../utils';

const imageFile = new File(['foo'], 'foo.jpg', {type: 'image/jpeg'});
const fileInfo = {
  originalFilename: 'foo.jpg',
  filePath: 'path/to/foo.jpg',
};

const xhrMock: Partial<XMLHttpRequest> = {
  open: jest.fn(),
  send: jest.fn(),
  setRequestHeader: jest.fn(),
  readyState: 4,
  status: 200,
  response: JSON.stringify(fileInfo),
  upload: {addEventListener: jest.fn((_eventName, callback) => callback({loaded: 50, total: 100}))} as any,
  addEventListener: jest.fn((_eventName, callback: EventListener) => callback(new Event('load'))),
};

jest.spyOn(window, 'XMLHttpRequest').mockImplementation(() => xhrMock as XMLHttpRequest);

test('It returns an image uploader that can upload a file', async () => {
  const {result} = renderHookWithProviders(() => useImageUploader('fake_route'));

  const uploader = result.current;
  const onProgress = jest.fn();

  const uploadedFile = await uploader(imageFile, onProgress);

  expect(xhrMock.open).toBeCalledWith('POST', 'fake_route', true);
  expect(xhrMock.setRequestHeader).toBeCalledWith('X-Requested-With', 'XMLHttpRequest');
  expect(onProgress).toBeCalledWith(0.5);
  expect(uploadedFile).toEqual(fileInfo);
});

test('It returns an image uploader that can handle failure', () => {
  jest.spyOn(window, 'XMLHttpRequest').mockImplementationOnce(
    () =>
      ({
        ...xhrMock,
        status: 500,
        response: 'Internal server error',
      } as XMLHttpRequest)
  );

  const {result} = renderHookWithProviders(() => useImageUploader('fake_route'));

  const uploader = result.current;
  const onProgress = jest.fn();

  expect(async () => {
    await uploader(imageFile, onProgress);
  }).rejects.toBe('Internal server error');
});
