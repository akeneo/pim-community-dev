// import React from "react";

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    return $.when(
      FormBuilder.build('akeneo-data-quality-insights-dqi-dashboard-index')
    ).then((form: any, data = []) => {
      form.setElement(this.$el).render();
      return form;
    });
  };
};

export = DqiDashboardController;
