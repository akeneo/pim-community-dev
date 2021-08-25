import {JobBreadcrumb} from './JobBreadcrumb';
import BaseForm = require('../../../view/base');

class Breadcrumb extends BaseForm {
  public render(): BaseForm {
    this.renderReact(
      JobBreadcrumb,
      {
        jobLabel: this.getFormData().label,
        jobType: this.getFormData().type
      },
      this.el
    );

    return this;
  }
}

export = Breadcrumb;
