import {DuplicateJob} from './DuplicateJob';

import BaseForm = require('../../../view/base');

type Config = {
  subTitle: string;
  editRoute: string;
};

class Duplicate extends BaseForm {
  private readonly config: Config;

  constructor(options: {config: Config}) {
    super({...options, ...{className: 'AknDropdown-menuLink', tagName: 'button'}});

    this.config = options.config;
  }

  public render(): BaseForm {
    this.renderReact(
      DuplicateJob,
      {
        jobCodeToDuplicate: this.getFormData().code,
        subTitle: this.config.subTitle,
        successRedirectRoute: this.config.editRoute,
      },
      this.el
    );

    return this;
  }
}

export = Duplicate;
