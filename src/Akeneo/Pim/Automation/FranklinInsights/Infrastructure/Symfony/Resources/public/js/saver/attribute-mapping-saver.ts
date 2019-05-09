/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const __ = require('oro/translator');
const BaseSaver = require('pim/form/common/save-form');
const Messenger = require('oro/messenger');

class AttributeMappingSaver extends BaseSaver {
  protected fail(response: any): void {
    let errorFlashMessage = '';

    switch (response.status) {
      case 400:
        this.getRoot().trigger(
          'pim_enrich:form:entity:bad_request',
          {'sentData': this.getFormData(), 'response': response.responseJSON}
        );

        errorFlashMessage = response.responseJSON
          .map((message:any) => __(message))
          .join('. ');
        break;
      case 500:
        const message = response.responseJSON ? response.responseJSON : response;

        console.error('Errors:', message);
        this.getRoot().trigger('pim_enrich:form:entity:error:save', message);

        errorFlashMessage = __('pim_enrich.entity.fallback.flash.update.fail');
        break;
      default:
    }

    Messenger.notify(
      'error',
      errorFlashMessage
    );
  }
}

export = AttributeMappingSaver;
