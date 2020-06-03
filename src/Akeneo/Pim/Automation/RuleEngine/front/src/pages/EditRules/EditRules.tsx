import React, { useState } from 'react';
import { ThemeProvider } from 'styled-components';
import * as akeneoTheme from '../../theme';
import { useBackboneRouter } from '../../dependenciesTools/hooks';
import { EditRulesContent } from './EditRulesContent';
import { FullScreenError } from '../../components/FullScreenError';
import { RuleDefinition } from '../../models';
import { useInitEditRules } from "./hooks";

type Props = {
  ruleDefinitionCode: string;
  setIsDirty: (isDirty: boolean) => void;
};

const EditRules: React.FC<Props> = ({ ruleDefinitionCode, setIsDirty }) => {
  const router = useBackboneRouter();
  const [ruleDefinition, setRuleDefinition] = useState<RuleDefinition>();
  const { error, locales, scopes } = useInitEditRules(
    ruleDefinitionCode,
    router,
    setRuleDefinition
  );

  return (
    <ThemeProvider theme={akeneoTheme}>
      {error.status ? (
        <FullScreenError
          statusCode={error.statusCode}
          message={error.exception.message}
        />
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
