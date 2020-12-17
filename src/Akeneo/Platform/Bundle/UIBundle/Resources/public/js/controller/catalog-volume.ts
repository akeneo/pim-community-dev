import * as $ from 'jquery';
import View from 'pimui/js/view/base-interface';

const BaseController = require('pim/controller/front');
const FormBuilder = require('pim/form-builder');
const Routing = require('routing');

class CatalogVolumeController extends BaseController {
  renderForm() {
    return $.when(
      FormBuilder.build('pim-catalog-volume-index'),
      $.get(Routing.generate('pim_volume_monitoring_get_volumes'))
    ).then((form: View & {setData: (data: any) => void}, data = []) => {
      this.on('pim:controller:can-leave', (event: {canLeave: true}) => {
        form.trigger('pim_enrich:form:can-leave', event);
      });

      form.setData(data[0]);
      form.setElement(this.$el).render();

      return form;
    });
  }
}

export default CatalogVolumeController;
