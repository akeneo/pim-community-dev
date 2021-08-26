import {JobBreadcrumb, JobBreadcrumbProps} from './JobBreadcrumb';
import BaseForm = require('../../../view/base');

type Config = {
  isEdit?: boolean;
};

class Breadcrumb extends BaseForm {
  private readonly config: Config;

  constructor(options: {config: Config}) {
    super({...options});

    this.config = options.config;
  }

  public render(): BaseForm {
    const {code, label, type} = this.getFormData();

    this.renderReact<JobBreadcrumbProps>(
      JobBreadcrumb,
      {
        isEdit: this.config.isEdit ?? false,
        jobLabel: label,
        jobType: type,
        jobCode: code,
      },
      this.el
    );

    return this;
  }
}

export = Breadcrumb;
