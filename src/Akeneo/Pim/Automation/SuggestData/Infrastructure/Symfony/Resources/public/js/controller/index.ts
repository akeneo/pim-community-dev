/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const BaseIndexController = require('pim/controller/common/index');
const FormBuilder = require('pim/form-builder');

/**
 * Front-end controller for Suggest Data context.
 * Can be use for any entity thanks to its configuration.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IndexController extends BaseIndexController {
  /**
   * {@inheritdoc}
   *
   * This is the same method than the parent, but adding the 'can-leave' mechanism.
   */
  public renderForm(): object {
    return FormBuilder.build('akeneo-suggest-data-' + this.options.config.entity + '-index')
      .then((form: any) => {
        this.on('pim:controller:can-leave', (event: any) => {
          form.trigger('pim_enrich:form:can-leave', event);
        });
        form.setElement(this.$el).render();
        return form;
      });
  }
}

export = IndexController;
