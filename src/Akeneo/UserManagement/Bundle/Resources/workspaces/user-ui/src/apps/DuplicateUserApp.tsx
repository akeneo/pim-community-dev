import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/shared';
import {ThemeProvider} from 'styled-components';
import {DuplicateUser} from '../pages';
import {UserCode, UserId} from '../models';

type DuplicateUserAppProps = {
  userId: UserId;
  userCode: UserCode;
  onCancel: () => void;
  onDuplicateSuccess: (userId: UserId) => void;
};

const DuplicateUserApp = ({userId, userCode, onCancel, onDuplicateSuccess}: DuplicateUserAppProps) => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <DuplicateUser
          userId={userId}
          userCode={userCode}
          onCancel={onCancel}
          onDuplicateSuccess={onDuplicateSuccess}
        />
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export default DuplicateUserApp;
