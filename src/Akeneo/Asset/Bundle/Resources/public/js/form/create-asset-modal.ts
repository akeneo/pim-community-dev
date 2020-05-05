import * as Backbone from 'backbone';
import * as _ from 'underscore';
import BaseView = require('pimui/js/view/base');

const Routing = require('routing');
const router = require('pim/router');
const Uploader = require('pimee/productasset/uploader');
const __ = require('oro/translator');

class CreateAssetModalView extends BaseView {
  private modal: any;
  readonly codeSelector: string = 'input[name="pimee_product_asset_create[code]"]'

  public events(): Backbone.EventsHash {
    return {
      'change input[type="file"]': 'populateCodeWithFilename',
      'switch-change .has-switch': 'toggleUploaderVisibility',
      'click .ok' : 'uploadAsset',
      'submit form': 'uploadAsset'
    };
  }

  public toggleUploaderVisibility(_: JQuery.Event, data: any): void {
    const referenceField = this.$el.find('.asset-uploader');
    const localizableField = this.$el.find('[name="pimee_product_asset_create[isLocalized]"]');

    if (true === data.value) {
      referenceField.hide('fast')
      referenceField.find('input[type="file"]').val('')
    } else {
      referenceField.show('fast');
    }

    localizableField.val(+data.value);
  }

  public populateCodeWithFilename(event: { currentTarget: any}): void {
    const uploadFieldValue = event.currentTarget.value;
    const codeField = this.$el.find(this.codeSelector);
    codeField.val(this.convertFilenameToCode(uploadFieldValue))
  }

  public getFormData(): FormData {
    const formData = new FormData();
    const inputData = this.$el.find('form').serializeArray();

    inputData.forEach(({ name, value }: { name: string, value: string }) => {
      formData.append(name, value);
    })

    const fileInput: any = this.$el.find('input[type="file"]').get(0);
    const files = fileInput.files

    if (undefined !== files) {
      formData.append('pimee_product_asset_create[reference_file][uploadedFile]', files[0]);
    }

    return formData;
  }

  public uploadAsset(event: JQuery.Event) {
    event.preventDefault();

    const codeInput: any = this.$el.find(this.codeSelector);
    const codeValue = codeInput.val();
    const error = this.getCodeValidationError(codeValue);

    if (null !== error) {
      this.showErrorTooltip(error);

      return;
    }

    return this.getNextAvailableCode(codeValue).then((nextCode: string | undefined) => {
      if (undefined === nextCode) {
        const data = this.getFormData();

        this.submitForm(data).then(({route, params}: { route: string, params: any}) => {
          router.redirectToRoute(route, params);
          this.modal.close();
        });
      } else {
        codeInput.val(nextCode);
        this.showErrorTooltip(__('pimee_product_asset.form.asset.unique'));
      }
    })
  }

  public open(): void {
    this.loadFormContents().then((response) => {
      const template = () => response;

      const BootstrapModal = (<any>Backbone).BootstrapModal;

      const modal = new BootstrapModal({
          className: 'modal mass-upload-modal',
          okText: '',
          okCloses: false,
          content: '<div></div>',
          template
      });

      modal.open();

      this.$el = modal.$el;
      this.modal = modal;

      this.setupPlugins();
      this.delegateEvents();
    })
  }

  private loadFormContents(): JQuery.jqXHR<string> {
    return $.get(Routing.generate('pimee_product_asset_create'));
  }

  private submitForm(data: FormData): JQuery.jqXHR<any> {
    return $.ajax({
      url: Routing.generate('pimee_product_asset_create'),
      type: 'post',
      data: data,
      contentType: false,
      cache: false,
      processData: false
    });
  }

  private getCodeValidationError(value: string): string | null {
    const valueIsEmpty = (!value || value.length === 0);
    const valueIsAlphaNumeric = (null === value.match('^[a-zA-Z0-9_]+$'));

    if (valueIsEmpty) {
      return __('pimee_product_asset.form.asset.not_empty');
    }

    if (valueIsAlphaNumeric) {
      return __('pimee_product_asset.form.asset.alpha_numeric_plus_underscore')
    }

    return null;
  }

  private showErrorTooltip(title: string): void {
    const errorContainer = this.$el.find('[name="pimee_product_asset_create[code]"] + .icons-container');

    const existingTooltip: any = errorContainer.find('.validation-tooltip')

    existingTooltip.tooltip('destroy');
    errorContainer.empty();

    const errorTooltip: any = $('<i/>').addClass('AknIconButton AknIconButton--important icon-warning-sign validation-tooltip');

    errorContainer.append(errorTooltip);
    errorTooltip.tooltip({ title, placement: 'right' });
  }

  private setupPlugins(): void {
    new Uploader();
    const toggleSwitch: any = this.$el.find('.switch');
    toggleSwitch.bootstrapSwitch()
  }

  private getNextAvailableCode(code: string): JQuery.Promise<string> {
    const nextCodeRoute = Routing.generate('pimee_product_asset_next_code', { code });
    return $.get(nextCodeRoute).then((data) => data.nextCode)
  }

  private convertFilenameToCode(filename: string): string {
    return filename
        .replace(/\\/g, '/')
        .replace(/.*\//, '')
        .replace(/\.[^/.]+$/, '')
        .replace(/[^A-Za-z0-9_]/g, '_');
  }
}

export = CreateAssetModalView;
