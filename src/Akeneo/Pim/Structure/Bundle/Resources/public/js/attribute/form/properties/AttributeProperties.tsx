import React from 'react';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import { AttributeDescriptionsApp } from '@akeneo-pim-community/settings-ui';

// const mediator = require('oro/mediator');

class AttributeProperties extends ReactView {
  // I think this component is not used at all :shrug:
  reactElementToMount() {
    return <AttributeDescriptionsApp
      onChange={() => {}}
      defaultValue={{en_US: 'TODO DeFAULT VALUE ENUS'}}
    />;
  }
}

export = AttributeProperties;
