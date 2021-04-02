import React, {useState} from 'react';
import {ThemeProvider} from 'styled-components';
import {useBackboneRouter, useTranslate} from '../../dependenciesTools/hooks';
import {EditRulesContent} from './EditRulesContent';
import {FullScreenError} from '../../components/FullScreenError';
import {RuleDefinition} from '../../models';
import {useInitEditRules} from './hooks';
import {AkeneoSpinner} from '../../components';
import {useSecurity} from '../../dependenciesTools/hooks/useSecurity';
import {pimTheme} from 'akeneo-design-system';

type Props = {
  ruleDefinitionCode: string;
  setIsDirty: (isDirty: boolean) => void;
};

const EditRules: React.FC<Props> = ({ruleDefinitionCode, setIsDirty}) => {
  const router = useBackboneRouter();
  const security = useSecurity();
  const translate = useTranslate();
  const [ruleDefinition, setRuleDefinition] = useState<RuleDefinition>();
  const {error, locales, uiLocales, scopes} = useInitEditRules(
    ruleDefinitionCode,
    router,
    security,
    translate,
    setRuleDefinition
  );

  return (
    <ThemeProvider theme={pimTheme}>
      {error.status ? (
        <FullScreenError
          statusCode={error.statusCode}
          message={error.exception.message}
        />
      ) : !ruleDefinition || !locales || !scopes || !uiLocales ? (
        <AkeneoSpinner />
      ) : (
        <EditRulesContent
          ruleDefinitionCode={ruleDefinitionCode}
          ruleDefinition={ruleDefinition}
          locales={locales}
          uiLocales={uiLocales}
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
