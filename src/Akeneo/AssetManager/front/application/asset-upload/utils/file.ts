import Line, {Thumbnail} from 'akeneoassetmanager/application/asset-upload/model/line';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';
import {NormalizedValidationError as ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import imageUploader from 'akeneoassetmanager/infrastructure/uploader/image';

type ThumbnailForLine = {
  thumbnail: Thumbnail;
  line: Line;
};

export const getThumbnailFromFile = async (file: File, line: Line): Promise<ThumbnailForLine> => {
  return new Promise((resolve: ({thumbnail, line}: ThumbnailForLine) => void) => {
    const fileReader = new FileReader();
    if (file.type.match('image')) {
      fileReader.onload = () => {
        resolve({thumbnail: fileReader.result as string, line});
      };
      fileReader.readAsDataURL(file);
    } else {
      resolve({thumbnail: null, line});
    }
  });
};

export const uploadFile = async (
  file: File,
  line: Line,
  updateProgress: (line: Line, progress: number) => void
): Promise<FileModel | null> => {
  return new Promise((resolve: (file: FileModel) => void, reject: (validation: ValidationError[]) => void) => {
    if (undefined === file) {
      resolve(null);
    }

    updateProgress(line, 0);

    try {
      imageUploader
        .upload(file, (ratio: number) => {
          updateProgress(line, ratio);
        })
        .then(resolve);
    } catch (error) {
      reject(error);
    }
  });
};
