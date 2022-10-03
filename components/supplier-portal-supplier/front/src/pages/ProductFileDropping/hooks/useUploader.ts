import {useBooleanState} from 'akeneo-design-system';
import {BadRequestError} from '../../../api/BadRequestError';

const uploadUrl = '/supplier-portal/product-file/upload';

type ErrorResponse = {error: string};

const useUploader = () => {
    const [isUploading, startUploading, stopUploading] = useBooleanState();

    const uploader = (file: File, onProgress: (ratio: number) => void): Promise<BadRequestError<ErrorResponse>> =>
        new Promise<BadRequestError<ErrorResponse>>((resolve, reject) => {
            const formData = new FormData();
            formData.append('file', file);
            startUploading();

            const xhr = new XMLHttpRequest();
            xhr.open('POST', uploadUrl, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.upload.addEventListener('progress', event => onProgress(event.loaded / event.total), false);
            xhr.addEventListener('load', () => {
                stopUploading();

                if (200 <= xhr.status && 300 > xhr.status) {
                    resolve(JSON.parse(xhr.response));
                } else {
                    try {
                        const response = JSON.parse(xhr.response);
                        reject(new BadRequestError(response ?? {}));
                    } catch (e) {
                        reject(new Error());
                    }
                }
            });
            xhr.send(formData);
        });

    return [uploader, isUploading] as const;
};

export {useUploader};
export type {ErrorResponse};
