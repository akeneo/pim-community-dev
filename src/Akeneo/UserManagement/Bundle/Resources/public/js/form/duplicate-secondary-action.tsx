import React from 'react';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DuplicateMenuLink} from './DuplicateMenuLink';

class DuplicateSecondaryAction extends ReactView {
  reactElementToMount() {
    return <DuplicateMenuLink userId={this.getFormData().meta.id} userCode={this.getFormData().code} />;
  }
}

export = DuplicateSecondaryAction;
