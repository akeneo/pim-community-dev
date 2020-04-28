import React from 'react';
import { Router, Translate } from '../../../dependenciesTools';
import { RuleProductSelection } from './RuleProductSelection';
import { RuleDefinition } from '../../../models';
import { Action } from '../../../models/Action';
import { Locale } from '../../../models';
import { IndexedScopes } from '../../../fetch/ScopeFetcher';
import { ActionLineProps } from '../ActionLineProps';

type Props = {
  translate: Translate;
  ruleDefinition: RuleDefinition;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: string;
  router: Router;
};

const ActionLine: React.FC<{ action: Action } & ActionLineProps> = ({
  action,
  translate,
  lineNumber,
}) => {
  const Line = action.module;

  return (
    <Line
      action={action}
      translate={translate}
      lineNumber={lineNumber}
    />
  );
};

const RulesBuilder: React.FC<Props> = ({
  translate,
  ruleDefinition,
  locales,
  scopes,
  currentCatalogLocale,
  router,
}) => {
  return (
    <>
      <RuleProductSelection
        ruleDefinition={ruleDefinition}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={currentCatalogLocale}
        router={router}
      />
      {ruleDefinition.actions.map((action, i) => {
        return (
          <ActionLine
            action={action}
            translate={translate}
            key={`action_${i}`}
            lineNumber={i}
          />
        );
      })}
    </>
  );
};

RulesBuilder.displayName = 'RulesBuilder';

export { RulesBuilder };
