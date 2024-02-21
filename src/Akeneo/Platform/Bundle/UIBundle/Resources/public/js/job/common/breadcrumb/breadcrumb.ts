import {JobBreadcrumb, JobBreadcrumbProps} from './JobBreadcrumb';
import BaseForm = require('../../../view/base');
const mediator = require('oro/mediator');

type Config = {
  isEdit?: boolean;
};

class Breadcrumb extends BaseForm {
  private readonly config: Config;

  constructor(options: {config: Config}) {
    super({...options});

    this.config = options.config;
  }

  /**
   * {@inheritdoc}
   */
  public configure() {
    mediator.trigger('pim_menu:highlight:tab', {extension: `pim-menu-${this.getFormData().type}s`});

    return BaseForm.prototype.configure.apply(this, arguments);
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
