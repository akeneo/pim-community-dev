import {act, renderHook} from '@testing-library/react-hooks';
import {useUploader} from './useUploader';

const excelFile = new File(['content'], 'foo.xlsx', {
    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
});
const fileInfo = {
    originalFilename: 'foo.xlsx',
    filePath: 'path/to/foo.xlsx',
};

test('It can upload a file', async () => {
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

    jest.spyOn(window, 'XMLHttpRequest').mockImplementationOnce(() => xhrMock as XMLHttpRequest);

    const {result} = renderHook(() => useUploader());

    const [uploader] = result.current;
    const onProgress = jest.fn();

    await act(async () => {
        const uploadedFile = await uploader(excelFile, onProgress);

        expect(xhrMock.open).toBeCalledWith('POST', '/supplier-portal/product-file/upload', true);
        expect(xhrMock.setRequestHeader).toBeCalledWith('X-Requested-With', 'XMLHttpRequest');
        expect(onProgress).toBeCalledWith(0.5);
        expect(uploadedFile).toEqual(fileInfo);
    });
});

test('It can handle failure', async () => {
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

    jest.spyOn(window, 'XMLHttpRequest').mockImplementationOnce(
        () =>
            ({
                ...xhrMock,
                status: 500,
                response: 'Internal server error',
            } as XMLHttpRequest)
    );

    const {result} = renderHook(() => useUploader());

    const [uploader] = result.current;
    const onProgress = jest.fn();
    await expect(async () => {
        await act(async () => {
            await uploader(excelFile, onProgress);
        });
    }).rejects.toStrictEqual(new Error());
});

test('It returns if an upload is in progress', () => {
    const timeoutXhrMock: Partial<XMLHttpRequest> = {
        open: jest.fn(),
        send: jest.fn(),
        setRequestHeader: jest.fn(),
        readyState: 4,
        status: 200,
        response: JSON.stringify(fileInfo),
        upload: {addEventListener: jest.fn((_eventName, callback) => callback({loaded: 50, total: 100}))} as any,
        addEventListener: jest.fn((_eventName, callback: EventListener) =>
            setTimeout(() => callback(new Event('load')), 10000)
        ),
    };

    jest.spyOn(window, 'XMLHttpRequest').mockImplementationOnce(() => timeoutXhrMock as XMLHttpRequest);

    jest.useFakeTimers();
    const {result} = renderHook(() => useUploader());

    let [uploader, isUploading] = result.current;
    const onProgress = jest.fn();

    expect(isUploading).toEqual(false);

    void act(() => {
        uploader(excelFile, onProgress);
    });

    [, isUploading] = result.current;
    expect(isUploading).toEqual(true);

    act(() => {
        jest.runAllTimers();
    });

    [, isUploading] = result.current;
    expect(isUploading).toEqual(false);
});
