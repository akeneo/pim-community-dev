import {FileInfo} from 'akeneo-design-system';
import {useRouter} from '@akeneo-pim-community/legacy-bridge';
import {useCallback, useState} from 'react';

const useImageUploader = (uploadRoute: string) => {
  const router = useRouter();
  const [uploadCount, setUploadCount] = useState<number>(0);
  const incrementUploadCount = () => setUploadCount(oldCount => oldCount + 1);
  const decrementUploadCount = () => setUploadCount(oldCount => oldCount - 1);
  const isUploading = () => uploadCount !== 0;

  const uploader = useCallback(
    (file: File, onProgress: (ratio: number) => void): Promise<FileInfo> =>
      new Promise<FileInfo>((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', file);
        incrementUploadCount();

        const xhr = new XMLHttpRequest();
        xhr.open('POST', router.generate(uploadRoute), true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.upload.addEventListener('progress', event => onProgress(event.loaded / event.total), false);
        xhr.addEventListener('load', () => {
          decrementUploadCount();

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

export {useImageUploader};
