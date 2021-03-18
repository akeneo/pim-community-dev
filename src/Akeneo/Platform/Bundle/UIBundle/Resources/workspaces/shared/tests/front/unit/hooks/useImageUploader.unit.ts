import {act} from '@testing-library/react-hooks';
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

const timeoutXhrMock: Partial<XMLHttpRequest> = {
  open: jest.fn(),
  send: jest.fn(() => setTimeout(() => this.onreadystatechange(), 10000)),
  setRequestHeader: jest.fn(),
  readyState: 4,
  status: 200,
  response: JSON.stringify(fileInfo),
  upload: {addEventListener: jest.fn((_eventName, callback) => callback({loaded: 50, total: 100}))} as any,
  addEventListener: jest.fn((_eventName, callback: EventListener) => (this.onreadystatechange = callback)),
};

jest.spyOn(window, 'XMLHttpRequest').mockImplementation(() => xhrMock as XMLHttpRequest);

test('It returns an image uploader that can upload a file', async () => {
  const {result} = renderHookWithProviders(() => useImageUploader('fake_route'));

  const [,uploader] = result.current;
  const onProgress = jest.fn();

  await act(async () => {
    const uploadedFile = await uploader(imageFile, onProgress);

    expect(xhrMock.open).toBeCalledWith('POST', 'fake_route', true);
    expect(xhrMock.setRequestHeader).toBeCalledWith('X-Requested-With', 'XMLHttpRequest');
    expect(onProgress).toBeCalledWith(0.5);
    expect(uploadedFile).toEqual(fileInfo);
  });
});

test('It returns an image uploader that can handle failure', async () => {
  jest.spyOn(window, 'XMLHttpRequest').mockImplementationOnce(
    () =>
      ({
        ...xhrMock,
        status: 500,
        response: 'Internal server error',
      } as XMLHttpRequest)
  );

  const {result} = renderHookWithProviders(() => useImageUploader('fake_route'));

  const [,uploader] = result.current;
  const onProgress = jest.fn();
  await expect(async () => {
    await act(async () => {
      await uploader(imageFile, onProgress);
    });
  }).rejects.toBe('Internal server error');
});

test('It returns if an upload is in progress', async () => {
  jest.spyOn(window, 'XMLHttpRequest').mockImplementationOnce(() => timeoutXhrMock as XMLHttpRequest);

  jest.useFakeTimers();
  const {result} = renderHookWithProviders(() => useImageUploader('fake_route'));

  let [isUploading, uploader] = result.current;
  const onProgress = jest.fn();

  expect(isUploading()).toEqual(false);

  void act(() => {
    uploader(imageFile, onProgress);
  });

  [isUploading] = result.current;
  expect(isUploading()).toEqual(true);

  await act(() => {
    jest.runAllTimers();
  });

  [isUploading] = result.current;
  expect(isUploading()).toEqual(false);
});
