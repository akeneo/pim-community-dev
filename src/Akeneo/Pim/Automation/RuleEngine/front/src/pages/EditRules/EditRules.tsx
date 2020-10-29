import React, {useState} from 'react';
import {ThemeProvider} from 'styled-components';
import * as akeneoTheme from '../../theme';
import {useBackboneRouter, useTranslate} from '../../dependenciesTools/hooks';
import {EditRulesContent} from './EditRulesContent';
import {FullScreenError} from '../../components/FullScreenError';
import {RuleDefinition} from '../../models';
import {useInitEditRules} from './hooks';
import {AkeneoSpinner} from '../../components';
import {useSecurity} from '../../dependenciesTools/hooks/useSecurity';

type Props = {
  ruleDefinitionCode: string;
  setIsDirty: (isDirty: boolean) => void;
};

const EditRules: React.FC<Props> = ({ruleDefinitionCode, setIsDirty}) => {
  const router = useBackboneRouter();
  const security = useSecurity();
  const translate = useTranslate();
  const [ruleDefinition, setRuleDefinition] = useState<RuleDefinition>();
  const {error, locales, scopes} = useInitEditRules(
    ruleDefinitionCode,
    router,
    security,
    translate,
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
        <AkeneoSpinner />
      ) : (
        <EditRulesContent
          ruleDefinitionCode={ruleDefinitionCode}
          ruleDefinition={ruleDefinition}
          locales={locales}
          scopes={scopes}
          setIsDirty={setIsDirty}
          security={security}
        />
      )}
    </ThemeProvider>
  );
};

EditRules.displayName = 'EditRules';

export {EditRules};
