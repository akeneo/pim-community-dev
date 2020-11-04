/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import View = require('pimui/js/view/base');
import * as _ from 'underscore';

const __ = require('oro/translator');

const SUPPORTED_OPERATIONS = ['edit_common', 'add_attribute_value'];

interface Model {
  operation: string;
  filters: any[];
}

class SendForApproval extends View {
  private template = _.template(`
    <input type="checkbox" id="send-for-approval"> <label for="send-for-approval">${__(
      'pimee_enrich.entity.product.module.approval.send'
    )}</label>
  `);

  public events() {
    return {
      'click #send-for-approval': () => {
        this.setData({
          actions: this.getFormData().actions.map((action: any) => ({
            ...action,
            sendForApproval: !action.sendForApproval,
          })),
        });
      },
    };
  }

  public configure(): any {
    super.configure();

    this.listenTo(this.getRoot(), 'mass-edit:action:confirm', this.onActionConfirm.bind(this));
  }

  private async onActionConfirm() {
    const model: Model = this.getFormData();

    if (!this.isOperationSupported(model.operation)) {
      return;
    }

    this.renderSendForApprovalCheckbox();
  }

  private isOperationSupported(operation: string): boolean {
    return undefined !== SUPPORTED_OPERATIONS.find(currentOperation => operation === currentOperation);
  }

  private renderSendForApprovalCheckbox(): void {
    this.$el.html(this.template({__}));

    const element = this.getRoot().$el.find('[data-action-target="validate"]');
    element.after(this.$el);

    this.delegateEvents();
  }
}

export = SendForApproval;
