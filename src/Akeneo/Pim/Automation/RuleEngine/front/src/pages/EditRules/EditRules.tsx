import React from 'react';
import { ThemeProvider } from 'styled-components';
import * as akeneoTheme from '../../theme';
import { useBackboneRouter } from '../../dependenciesTools/hooks';
import { useInitEditRules } from '../EditRules';
import { EditRulesContent } from './EditRulesContent';

type Props = {
  ruleDefinitionCode: string;
  setIsDirty: (isDirty: boolean) => void;
};

const EditRules: React.FC<Props> = ({ ruleDefinitionCode, setIsDirty }) => {
  const router = useBackboneRouter();
  const { error, ruleDefinition, locales, scopes } = useInitEditRules(
    ruleDefinitionCode,
    router
  );
  return (
    <ThemeProvider theme={akeneoTheme}>
      {error.status ? (
        'There was an error (TODO: better display)'
      ) : !ruleDefinition || !locales || !scopes ? (
        'Loading (TODO: better display)'
      ) : (
        <EditRulesContent
          ruleDefinitionCode={ruleDefinitionCode}
          ruleDefinition={ruleDefinition}
          locales={locales}
          scopes={scopes}
          setIsDirty={setIsDirty}
        />
      )}
    </ThemeProvider>
  );
};

EditRules.displayName = 'EditRules';

export { EditRules };
