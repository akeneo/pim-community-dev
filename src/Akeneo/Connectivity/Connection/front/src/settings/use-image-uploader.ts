import * as $ from 'jquery';
import {useRoute} from '../shared/router';
import {err, ok, Result} from '../shared/fetch-result/result';

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

export const useImageUploader = (handleOnUpload: (e: {loaded: number; total: number}) => void) => {
    const url = useRoute('pim_enrich_media_rest_post');

    return async (file: File): Promise<Result<UploadedImage, UploadError>> => {
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

        try {
            const result = await $.ajax({
                url,
                type: 'POST',
                data: body,
                contentType: false,
                cache: false,
                processData: false,
                xhr: () => {
                    const ajaxSettings = $.ajaxSettings as any;
                    const myXhr = ajaxSettings.xhr();
                    if (myXhr.upload) {
                        myXhr.upload.addEventListener('progress', handleOnUpload, false);
                    }

                    return myXhr;
                },
            });

            return ok<UploadedImage>(result);
        } catch (e) {
            return err(e.responseJSON);
        }
    };
};
