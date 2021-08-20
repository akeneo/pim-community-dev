import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {DuplicateJob} from './DuplicateJob';

import BaseForm = require('../../../view/base');

const SecurityContext = require('pim/security-context');

interface Config {
  labels: {
    subTitle: string;
  };
  editRoute: string;
}

class Duplicate extends BaseForm {
  private readonly config: Config;

  constructor(options: {config: Config}) {
    super({...options, ...{className: 'AknDropdown-menuLink duplicate', tagName: 'button'}});

    this.config = options.config;
  }

  public render(): BaseForm {
    // Check if this check is necessary. yml configuration should be doing it for us
    if (SecurityContext.isGranted('pim_importexport_export_profile_create')) {
      this.renderDuplicateButtonAndModal();
    }

    return BaseForm.prototype.render.apply(this, arguments);
  }

  public renderDuplicateButtonAndModal(): void {
    ReactDOM.render(
      React.createElement(
        ThemeProvider,
        {theme: pimTheme},
        React.createElement(
          DependenciesProvider,
          null,
          React.createElement(DuplicateJob, {
            jobCodeToDuplicate: this.getFormData().code,
            subTitle: this.config.labels.subTitle,
            successRedirectRoute: this.config.editRoute,
          }),
        ),
      ),
      this.el,
    );
  }
}

export = Duplicate;
