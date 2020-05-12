import React from 'react';
import { Router, Translate } from '../../../dependenciesTools';
import { RuleProductSelection } from './conditions/RuleProductSelection';
import { RuleDefinition, Locale } from '../../../models';
import { Action } from '../../../models/Action';
import { IndexedScopes } from '../../../repositories/ScopeRepository';
import { useFormContext } from 'react-hook-form';
import { ActionLine } from './actions/ActionLine';

type Props = {
  translate: Translate;
  ruleDefinition: RuleDefinition;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: string;
  router: Router;
};

const RulesBuilder: React.FC<Props> = ({
  translate,
  ruleDefinition,
  locales,
  scopes,
  currentCatalogLocale,
  router,
}) => {
  const [actions, setActions] = React.useState<(Action | null)[]>(
    ruleDefinition.actions
  );

  const { getValues, unregister } = useFormContext();
  const deleteAction = (lineNumber: number) => {
    Object.keys(getValues()).forEach((value: string) => {
      if (value.startsWith(`content.actions[${lineNumber}]`)) {
        unregister(value);
      }
    });
    setActions(
      actions.map((action: Action | null, i: number) => {
        return i === lineNumber ? null : action;
      })
    );
  };

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
      {actions.map((action: Action | null, i) => {
        return (
          action && (
            <ActionLine
              action={action}
              translate={translate}
              key={`action_${i}`}
              lineNumber={i}
              handleDelete={() => {
                deleteAction(i);
              }}
            />
          )
        );
      })}
    </>
  );
};

RulesBuilder.displayName = 'RulesBuilder';

export { RulesBuilder };
