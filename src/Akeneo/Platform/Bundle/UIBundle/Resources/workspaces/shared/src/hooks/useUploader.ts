import {FileInfo, useBooleanState} from 'akeneo-design-system';
import {useRouter} from '@akeneo-pim-community/legacy-bridge';
import {useCallback} from 'react';

const useUploader = (uploadRoute: string) => {
  const router = useRouter();
  const [isUploading, startUploading, stopUploading] = useBooleanState();

  const uploader = useCallback(
    (file: File, onProgress: (ratio: number) => void): Promise<FileInfo> =>
      new Promise<FileInfo>((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', file);
        startUploading();

        const xhr = new XMLHttpRequest();
        xhr.open('POST', router.generate(uploadRoute), true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.upload.addEventListener('progress', event => onProgress(event.loaded / event.total), false);
        xhr.addEventListener('load', () => {
          stopUploading();

          if (xhr.status === 200) {
            resolve(JSON.parse(xhr.response));
          } else {
            reject(xhr.response || []);
          }
        });
        xhr.send(formData);
      }),
    [router, uploadRoute]
  );

  return [isUploading, uploader] as const;
};

export {useUploader};
