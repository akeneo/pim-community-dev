import React from 'react';
import { RuleProductSelection } from './conditions/RuleProductSelection';
import { Locale, LocaleCode } from '../../../models';
import { IndexedScopes } from '../../../repositories/ScopeRepository';
import { ActionLine } from './actions/ActionLine';
import styled from 'styled-components';
import middleImage from '../../../assets/illustrations/middle.svg';
import endImage from '../../../assets/illustrations/end.svg';
import { Action } from "../../../models/Action";

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
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: LocaleCode;
  actions: ({[key: string]: any} & { id?: string })[];
  handleDeleteAction: (lineNumber: number) => void;
};

const RulesBuilder: React.FC<Props> = ({
  locales,
  scopes,
  currentCatalogLocale,
  actions,
  handleDeleteAction,
}) => {
  const isLastAction: (lineNumber: number) => boolean = lineNumber => {
    return lineNumber === actions.length - 1;
  };

  return (
    <>
      <RuleProductSelection
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={currentCatalogLocale}
      />
      <div data-testid={'action-list'}>
        {actions.map((action, i) => {
          const Component = isLastAction(i)
            ? LastActionContainer
            : ActionContainer;
          return (
            <Component key={action.id}>
              <ActionLine
                action={action as Action}
                lineNumber={i}
                handleDelete={() => {
                  handleDeleteAction(i);
                }}
                currentCatalogLocale={currentCatalogLocale}
                locales={locales}
                scopes={scopes}
              />
            </Component>
          );
        })}
      </div>
    </>
  );
};

RulesBuilder.displayName = 'RulesBuilder';

export { RulesBuilder };
