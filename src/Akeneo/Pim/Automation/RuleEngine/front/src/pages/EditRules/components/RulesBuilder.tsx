import React from 'react';
import { Router, Translate } from '../../../dependenciesTools';
import { RuleProductSelection } from './conditions/RuleProductSelection';
import { RuleDefinition, Locale, LocaleCode } from '../../../models';
import { Action } from '../../../models/Action';
import { IndexedScopes } from '../../../repositories/ScopeRepository';
import { ActionLine } from './actions/ActionLine';
import styled from 'styled-components';
import middleImage from '../../../assets/illustrations/middle.svg';
import endImage from '../../../assets/illustrations/end.svg';

const ActionContainer = styled.div`
  background-image: url('${middleImage}');
  padding-left: 12px;
  margin-left: -12px;
  background-repeat: no-repeat;  
  padding-bottom: 20px;
`;

const LastActionContainer = styled.div`
  background-image: url('${endImage}');
  padding-left: 12px;
  margin-left: -12px;
  background-repeat: no-repeat;  
`;

type Props = {
  translate: Translate;
  ruleDefinition: RuleDefinition;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: LocaleCode;
  router: Router;
  actions: (Action | null)[];
  handleDeleteAction: (lineNumber: number) => void;
};

const RulesBuilder: React.FC<Props> = ({
  translate,
  ruleDefinition,
  locales,
  scopes,
  currentCatalogLocale,
  router,
  actions,
  handleDeleteAction,
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
      {actions.map((action: Action | null, i) => {
        const Component =
          i === actions.length - 1 ? LastActionContainer : ActionContainer;
        return (
          action && (
            <Component key={`action_${i}`}>
              <ActionLine
                action={action}
                translate={translate}
                lineNumber={i}
                handleDelete={() => {
                  handleDeleteAction(i);
                }}
                router={router}
                currentCatalogLocale={currentCatalogLocale}
              />
            </Component>
          )
        );
      })}
    </>
  );
};

RulesBuilder.displayName = 'RulesBuilder';

export { RulesBuilder };
