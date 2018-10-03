import BaseView = require('pimenrich/js/view/base');

const BaseController = require('pim/controller/front');
const FormBuilder = require('pim/form-builder');
const FetcherRegistry = require('pim/fetcher-registry');

interface Route {
  name: string;
  params: { familyCode: string };
  route: object;
}

interface FamilyMapping {
  code: string;
  enabled: boolean;
  mapping: Array<{[index: string]: string}>;
}

interface CanLeaveEvent {
  canLeave: boolean;
}

/**
 * Attribute mapping edit controller
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class EditAttributeMappingController extends BaseController {
  public renderForm(route: Route): BaseView {
    return FetcherRegistry
      .getFetcher('suggest_data_attribute_mapping_by_family')
      .fetch(route.params.familyCode, {cached: false})
      .then((familyMapping: FamilyMapping) => {
        if (!this.active) {
          return;
        }

        return FormBuilder.build('pim-suggest-data-settings-attributes-mapping-edit')
          .then((form: BaseView) => {
            this.on('pim:controller:can-leave', (event: CanLeaveEvent) => {
              form.trigger('pim_enrich:form:can-leave', event);
            });
            form.setData(familyMapping);
            form.trigger('pim_enrich:form:entity:post_fetch', familyMapping);
            form.setElement(this.$el).render();

            return form;
          });
      });
  }
}

export = EditAttributeMappingController;
