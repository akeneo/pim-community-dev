import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {AttributeDescriptions} from '../components';

type AttributeDescriptionsAppProps = {
  defaultValue: {[key: string]: string};
  onChange: (defaultValue: {[key: string]: string}) => void;
};

const AttributeDescriptionsApp = ({defaultValue, onChange}: AttributeDescriptionsAppProps) => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AttributeDescriptions defaultValue={defaultValue} onChange={onChange} />
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {AttributeDescriptionsApp};
