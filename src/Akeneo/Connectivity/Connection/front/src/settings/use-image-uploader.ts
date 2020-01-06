import {fetchResult} from '../shared/fetch-result';
import {useRoute} from '../shared/router';
import {err} from '../shared/fetch-result/result';

export interface UploadedImage {
    originalFilename: string;
    filePath: string;
}

export interface UploadError {
    [propertyPath: string]: {
        message: string;
        invalid_value: string;
    };
}

const allowedExtensions = [
    'image/jpg',
    'image/jpeg',
    'image/gif',
    'image/png',
    'image/wbmp',
    'image/xbm',
    'image/webp',
    'image/bmp',
];

export const useImageUploader = () => {
    const url = useRoute('pim_enrich_media_rest_post');

    return async (file: File) => {
        if (!allowedExtensions.includes(file.type)) {
            return err({
                extension: {
                    message: 'akeneo_connectivity.connection.edit_connection.image_uploader.extension_not_allowed',
                    invalid_value: file.type,
                },
            });
        }
        const body = new FormData();
        body.append('file', file);

        return await fetchResult<UploadedImage, UploadError>(url, {
            method: 'POST',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
            body: body,
        });
    };
};
