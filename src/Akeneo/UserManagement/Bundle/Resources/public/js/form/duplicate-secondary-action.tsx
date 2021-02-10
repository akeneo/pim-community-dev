import React from 'react';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DuplicateOption} from './DuplicateOption';

class DuplicateSecondaryAction extends ReactView {
  reactElementToMount() {
    return <DuplicateOption userId={this.getFormData().meta.id} />;
  }
}

export = DuplicateSecondaryAction;
