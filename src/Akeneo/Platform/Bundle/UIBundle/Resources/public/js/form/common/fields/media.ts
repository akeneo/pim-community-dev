import * as $ from 'jquery';
const _ = require('underscore');
const __ = require('oro/translator');
const BaseField = require('pim/form/common/fields/field');
const Routing = require('routing');
const Mediator = require('oro/mediator');
const Messenger = require('oro/messenger');
const MediaUrlGenerator = require('pim/media-url-generator');
const template = require('pim/template/form/common/fields/media');
require('jquery.slimbox');

/**
 * Media field (using back-end FileInfo)
 *
 * @author    Marie Gautier <marie.gautier@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MediaField extends BaseField {
  readonly template = _.template(template);

  constructor(config: any) {
    super(config);

    this.events = {
      'change input[type="file"]': this.uploadMedia,
      'click .clear-field': this.clearField,
      'click .open-media': this.openMedia
    };
  }

  /**
   * {@inheritdoc}
   */
  getTemplateContext() {
    return BaseField.prototype.getTemplateContext.apply(this, arguments)
      .then((templateContext: any) => {
        templateContext.mediaUrlGenerator = MediaUrlGenerator;

        return templateContext;
      });
  }

  /**
   * {@inheritdoc}
   */
  renderInput(templateContext: any) {
    return this.template(Object.assign(templateContext, {
      media: this.getFormData()[this.fieldName],
      readOnly: this.readOnly,
      uploadLabel: __('pim_common.media_upload')
    }));
  }

  private handleProcess(e: { loaded: number, total: number }) {
    this.$('> .akeneo-media-uploader-field .progress').css({opacity: 1});
    this.$('> .akeneo-media-uploader-field .progress .bar').css({
      width: ((e.loaded / e.total) * 100) + '%'
    });
  }

  private setUploadContextValue(value: any) {
    this.updateModel(value);

    Mediator.trigger('pim_enrich:form:entity:update_state');
  }

  private uploadMedia() {
    const input = this.$('input[type="file"]')[0];
    if (!input || 0 === input.files.length) {
      return;
    }

    const formData = new FormData();
    formData.append('file', input.files[0]);

    $.ajax({
      url: Routing.generate('pim_enrich_media_rest_post'),
      type: 'POST',
      data: formData,
      contentType: false,
      cache: false,
      processData: false,
      xhr: () => {
        const myXhr = (<any> $.ajaxSettings).xhr();
        if (myXhr.upload) {
          myXhr.upload.addEventListener('progress', this.handleProcess.bind(this), false);
        }

        return myXhr;
      }
    }).done((data) => {
      this.setUploadContextValue(data);
      this.render();
    }).fail((xhr) => {
      const message = xhr.responseJSON && xhr.responseJSON.message ?
        xhr.responseJSON.message :
        __('pim_enrich.entity.product.error.upload');
      Messenger.enqueueMessage('error', message);
    }).always(() => {
      this.$('> .akeneo-media-uploader-field .progress').css({opacity: 0});
    });
  }

  private clearField() {
    this.updateModel({
      filePath: null,
      originalFilename: null
    });

    this.render();
  }

  private openMedia() {
    const mediaUrl = MediaUrlGenerator.getMediaShowUrl(this.getFormData()[this.fieldName].filePath, 'preview');
    if (mediaUrl) {
      (<any> $).slimbox(mediaUrl, '', {overlayOpacity: 0.3});
    }
  }
}

export = MediaField
