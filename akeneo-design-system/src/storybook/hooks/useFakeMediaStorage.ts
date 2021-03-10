import {useState} from 'react';
import {FileInfo} from '../../components';

const useFakeMediaStorage = () => {
  const [uploadedFile, setUploadedFile] = useState<string | null>(null);

  const uploader = (file: File, onProgress: (ratio: number) => void): Promise<FileInfo> =>
    new Promise(resolve => {
      const normalizedFile = URL.createObjectURL(file);
      setUploadedFile(normalizedFile);

      let progress = 0;
      const interval = setInterval(() => {
        onProgress(++progress / 10);
      }, 100);

      setTimeout(() => {
        clearInterval(interval);

        resolve({
          filePath: `/file/${file.name}`,
          originalFilename: file.name,
        });
      }, 1000);
    });

  const downloader = () => uploadedFile;
  const previewer = () => uploadedFile;

  return [uploader, downloader, previewer];
};

export {useFakeMediaStorage};
