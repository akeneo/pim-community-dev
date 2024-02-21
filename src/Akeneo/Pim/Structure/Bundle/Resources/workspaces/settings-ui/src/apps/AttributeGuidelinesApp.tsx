import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {AttributeGuidelines} from '../components';

type AttributeGuidelinesAppProps = {
  defaultValue: {[key: string]: string};
  onChange: (defaultValue: {[key: string]: string}) => void;
};

const AttributeGuidelinesApp = ({defaultValue, onChange}: AttributeGuidelinesAppProps) => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AttributeGuidelines defaultValue={defaultValue} onChange={onChange} />
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {AttributeGuidelinesApp};
