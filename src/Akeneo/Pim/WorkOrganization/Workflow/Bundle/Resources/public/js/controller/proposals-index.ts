/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as $ from 'jquery';

const BaseController = require('pim/controller/front');
const FormBuilder = require('pim/form-builder');

/**
 * Loads the grid and put the filters module under the navigation menu.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProposalIndexController extends BaseController {
  private filterForm: any;

  /**
   * {@inheritdoc}
   */
  public renderForm(): any {
    return FormBuilder.build('pim-proposal-index')
      .then((form: any) => {
        form.setElement(this.$el).render();

        this.filterForm = FormBuilder.build('pim-proposal-index-grid-filters')
          .then((filterForm: any) => {
            filterForm.render();
            filterForm.$el.insertAfter($('.AknColumn-innerTop[data-drop-zone=navigation] .AknColumn-block'));

            return filterForm;
          });

        return form;
      });
  }

  /**
   * {@inheritdoc}
   */
  public remove(): void {
    if (null !== this.filterForm) {
      this.filterForm.then((form: any) => {
        form.shutdown();
      });
    }

    BaseController.prototype.remove.apply(this, arguments);
  }
}

export = ProposalIndexController;
