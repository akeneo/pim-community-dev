import {JobBreadcrumb} from './JobBreadcrumb';
import BaseForm = require('../../../view/base');

type Config = {
  isEdit: boolean;
};

class Breadcrumb extends BaseForm {
  private readonly config: Config;

  constructor(options: {config: Config}) {
    super({...options});

    this.config = options.config;
  }

  public render(): BaseForm {
    this.renderReact(
      JobBreadcrumb,
      {
        isEdit: this.config.isEdit,
        jobLabel: this.getFormData().label,
        jobType: this.getFormData().type
      },
      this.el
    );

    return this;
  }
}

export = Breadcrumb;
