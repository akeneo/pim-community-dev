import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {DuplicateUser} from '../pages';

type DuplicateUserAppProps = {
  userId: number;
  onCancel: () => void;
  onDuplicateSuccess: (userId: string) => void;
};

const DuplicateUserApp = ({userId, onCancel, onDuplicateSuccess}: DuplicateUserAppProps) => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <DuplicateUser userId={userId} onCancel={onCancel} onDuplicateSuccess={onDuplicateSuccess} />
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export default DuplicateUserApp;
