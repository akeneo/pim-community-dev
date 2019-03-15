import * as _ from 'underscore';

const BaseTextarea = require('pim/authentication/sso/configuration/fields/textarea');
const template = require('pim/template/form/common/fields/textarea-monospaced');
const __ = require('oro/translator');

class SpCertificate extends BaseTextarea {
  readonly template = _.template(template);

  public configure() {
    this.listenTo(this.getRoot(), this.postUpdateEventName, (data: any) => {
      const expirationDate = data.meta.service_provider_certificate_expiration_date;

      if (null === expirationDate || undefined === expirationDate) {
        return;
      }

      const expiresSoon = this.getFormData().meta.service_provider_certificate_expires_soon;
      const expirationMessage = __('authentication.sso.configuration.field.certificate.expiration_warning')
        .replace('{{date}}', expirationDate);

      if (expiresSoon) {
        this.errors = [{message: expirationMessage}];
      } else {
        this.warnings = [{message: expirationMessage}];
      }
    });

    return super.configure();
  }
}

export = SpCertificate;
