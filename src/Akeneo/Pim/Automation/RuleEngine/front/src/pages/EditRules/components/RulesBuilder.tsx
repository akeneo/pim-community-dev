import React from 'react';
import { useFormContext } from 'react-hook-form';
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
  register,
}) => {
  const Line = action.module;

  return (
    <Line
      action={action}
      translate={translate}
      lineNumber={lineNumber}
      register={register}
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
  const { register } = useFormContext();
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
            register={register}
          />
        );
      })}
    </>
  );
};

RulesBuilder.displayName = 'RulesBuilder';

export { RulesBuilder };
