import Uploader from 'akeneoreferenceentity/domain/uploader/uploader';
import Image, {NormalizedFile, denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import * as $ from 'jquery';
const routing = require('routing');

export class ConcreteImageUploader implements Uploader<Image> {
  private constructor(readonly jQuery: any, readonly router: any, readonly route: string) {
    Object.freeze(this);
  }

  static create(jQuery: any, router: any, route: any) {
    return new ConcreteImageUploader(jQuery, router, route);
  }

  upload(file: File, onProgress: (ratio: number) => void) {
    return new Promise<Image>((resolve: any, reject: any) => {
      const formData = new FormData();
      formData.append('file', file);

      this.jQuery
        .ajax({
          url: this.router.generate(this.route),
          type: 'POST',
          data: formData,
          contentType: false,
          cache: false,
          processData: false,
          xhr: () => {
            const xhr = this.jQuery.ajaxSettings.xhr();
            if (xhr.upload) {
              xhr.upload.addEventListener(
                'progress',
                (event: any) => onProgress(event.loaded / event.matchesCount),
                false
              );
            }

            return xhr;
          },
        })
        .then((normalizedFile: NormalizedFile) => {
          resolve(denormalizeFile(normalizedFile));
        })
        .fail((response: any) => {
          reject(response.responseJSON);
        });
    });
  }
}

export default ConcreteImageUploader.create($, routing, 'pim_enrich_media_rest_post');
