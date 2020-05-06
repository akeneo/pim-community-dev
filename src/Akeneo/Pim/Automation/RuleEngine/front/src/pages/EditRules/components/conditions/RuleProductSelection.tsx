import React from 'react';
import styled from 'styled-components';
import { Router, Translate } from '../../../../dependenciesTools';
import { SmallHelper } from '../../../../components';
import { TextBoxBlue } from '../TextBoxBlue';
import { ConditionFactory, RuleDefinition } from '../../../../models/';
import { Condition } from '../../../../models/';
import { Locale } from '../../../../models/';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';
import { useFormContext } from 'react-hook-form';
import { ConditionLine } from './ConditionLine';
import { AddConditionButton } from './AddConditionButton';
import { createFamilyCondition } from '../../../../models/FamilyCondition';
import { createTextAttributeCondition } from '../../../../models/TextAttributeCondition';

const Header = styled.header`
  font-weight: normal;
  margin-bottom: 0;
  width: 100%;
`;

const LegendSrOnly = styled.legend`
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
`;

const HeaderPartContainer = styled.span`
  display: flex;
  align-items: center;
`;

const TitleHeader = styled.span`
  padding-left: 8px;
`;

const AddConditionContainer = styled.div`
  border-left: 1px solid ${({ theme }) => theme.color.grey100};
  display: flex;
  margin-left: 15px;
  padding-left: 15px;
`;

type Props = {
  ruleDefinition: RuleDefinition;
  translate: Translate;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: string;
  router: Router;
};

const RuleProductSelection: React.FC<Props> = ({
  ruleDefinition,
  translate,
  locales,
  scopes,
  currentCatalogLocale,
  router,
}) => {
  const [conditions, setConditions] = React.useState<(Condition | null)[]>(
    ruleDefinition.conditions
  );

  const { getValues, unregister } = useFormContext();
  const deleteCondition = (lineNumber: number) => {
    Object.keys(getValues()).forEach((value: string) => {
      if (value.startsWith(`content.conditions[${lineNumber}]`)) {
        unregister(value);
      }
    });
    setConditions(
      conditions.map((condition: Condition | null, i: number) => {
        return i === lineNumber ? null : condition;
      })
    );
  };

  async function createCondition(fieldCode: string): Promise<Condition> {
    const factories: ConditionFactory[] = [
      createFamilyCondition,
      createTextAttributeCondition,
    ];

    for (let i = 0; i < factories.length; i++) {
      const factory = factories[i];
      const condition = await factory(fieldCode, router);
      if (condition !== null) {
        return condition;
      }
    }

    throw new Error(`Unknown factory for field ${fieldCode}`);
  }

  const handleAddCondition = (fieldCode: string) => {
    createCondition(fieldCode).then(condition => {
      const newConditions = [...conditions, condition];
      setConditions(newConditions);
    });
  };

  return (
    <fieldset>
      <Header className='AknSubsection-title'>
        <HeaderPartContainer>
          <TextBoxBlue>
            {translate('pimee_catalog_rule.rule.condition.if.label')}
          </TextBoxBlue>
          <TitleHeader>
            {translate('pimee_catalog_rule.form.edit.product_selection')}
          </TitleHeader>
        </HeaderPartContainer>
        <HeaderPartContainer>
          <span className='AknSubsection-comment AknSubsection-comment--clickable'>
            {translate('pimee_catalog_rule.form.edit.count_products', {
              count: '0',
            })}
          </span>
          <AddConditionContainer>
            <AddConditionButton
              router={router}
              handleAddCondition={handleAddCondition}
              translate={translate}
            />
          </AddConditionContainer>
        </HeaderPartContainer>
      </Header>
      <SmallHelper>
        {translate('pimee_catalog_rule.form.helper.product_selection')}
        <a href='#'>
          {translate(
            'pimee_catalog_rule.form.helper.product_selection_doc_link'
          )}
        </a>
      </SmallHelper>
      <div className='AknGrid AknGrid--unclickable'>
        <div className='AknGrid-body'>
          {conditions.map((condition, i) => {
            return (
              condition && (
                <ConditionLine
                  condition={condition}
                  lineNumber={i}
                  translate={translate}
                  key={`condition_${i}`}
                  locales={locales}
                  scopes={scopes}
                  currentCatalogLocale={currentCatalogLocale}
                  router={router}
                  deleteCondition={deleteCondition}
                />
              )
            );
          })}
        </div>
      </div>
      <LegendSrOnly>
        {translate('pimee_catalog_rule.form.legend.product_selection')}
      </LegendSrOnly>
    </fieldset>
  );
};

RuleProductSelection.displayName = 'RuleProductSelection';

export { RuleProductSelection };
