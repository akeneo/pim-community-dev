import React from 'react';
import { RuleProductSelection } from './conditions/RuleProductSelection';
import { Condition, Locale, LocaleCode } from '../../../models';
import { IndexedScopes } from '../../../repositories/ScopeRepository';
import { ActionLine } from './actions/ActionLine';
import { Action } from '../../../models/Action';
import { EmptySectionMessage } from './EmptySectionMessage';

type Props = {
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: LocaleCode;
  handleDeleteAction: (lineNumber: number) => void;
  handleAddCondition: (condition: Condition) => void;
  handleDeleteCondition: (lineNumber: number) => void;
  conditions: Condition[];
  actions: (Action | null)[];
};

const RulesBuilder: React.FC<Props> = ({
  locales,
  scopes,
  currentCatalogLocale,
  actions,
  handleDeleteAction,
  conditions,
  handleAddCondition,
  handleDeleteCondition,
}) => {
  return (
    <>
      <RuleProductSelection
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={currentCatalogLocale}
        conditions={conditions}
        handleAdd={handleAddCondition}
        handleDelete={handleDeleteCondition}
      />
      <div data-testid={'action-list'} className={'actionList'}>
        {actions.map((action, i) => {
          return (
            action && (
              <ActionLine
                action={action}
                lineNumber={i}
                handleDelete={() => {
                  handleDeleteAction(i);
                }}
                currentCatalogLocale={currentCatalogLocale}
                locales={locales}
                scopes={scopes}
                key={i}
              />
            )
          );
        })}
      </div>
      {!!conditions.filter(Boolean).length && !actions.filter(Boolean).length && (
        <EmptySectionMessage>
          <div>
            Well done! You defined your product selection. Now it's time to set up your action.
          </div>
        </EmptySectionMessage>
      )}
    </>
  );
};

RulesBuilder.displayName = 'RulesBuilder';

export { RulesBuilder };
