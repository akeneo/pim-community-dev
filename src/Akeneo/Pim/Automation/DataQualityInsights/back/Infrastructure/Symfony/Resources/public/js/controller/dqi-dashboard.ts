// import React from "react";

const BaseController = require('pim/controller/front');
const FormBuilder = require('pim/form-builder');

/**
 * @author Anais Baune Lemaire <anais.lemaire@akeneo.com>
 */
class DqiDashboardController extends BaseController {
  /**
   * {@inheritdoc}
   */
  public renderForm() {
    return $.when(FormBuilder.build('akeneo-data-quality-insights-dqi-dashboard-index')).then((form: any, _ = []) => {
      form.setElement(this.$el).render();
      return form;
    });
  }
}

export = DqiDashboardController;
