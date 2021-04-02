import React from 'react';
import {RuleProductSelection} from './conditions/RuleProductSelection';
import {Condition, Locale, LocaleCode} from '../../../models';
import {IndexedScopes} from '../../../repositories/ScopeRepository';
import {ActionLine} from './actions/ActionLine';
import {Action} from '../../../models/Action';
import {EmptySectionMessage} from './EmptySectionMessage';
import {useTranslate} from '../../../dependenciesTools/hooks';

type Props = {
  locales: Locale[];
  uiLocales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: LocaleCode;
  handleDeleteAction: (lineNumber: number) => void;
  handleAddCondition: (condition: Condition) => void;
  handleDeleteCondition: (lineNumber: number) => void;
  conditions: (Condition | null)[];
  actions: (Action | null)[];
};

const RulesBuilder: React.FC<Props> = ({
  locales,
  uiLocales,
  scopes,
  currentCatalogLocale,
  actions,
  handleDeleteAction,
  conditions,
  handleAddCondition,
  handleDeleteCondition,
}) => {
  const translate = useTranslate();

  return (
    <>
      <RuleProductSelection
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={currentCatalogLocale}
        conditions={conditions}
        handleAddCondition={handleAddCondition}
        handleDeleteCondition={handleDeleteCondition}
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
                uiLocales={uiLocales}
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
            {translate(
              'pimee_catalog_rule.form.edit.empty_section.set_up_action'
            )}
          </div>
        </EmptySectionMessage>
      )}
    </>
  );
};

RulesBuilder.displayName = 'RulesBuilder';

export {RulesBuilder};
