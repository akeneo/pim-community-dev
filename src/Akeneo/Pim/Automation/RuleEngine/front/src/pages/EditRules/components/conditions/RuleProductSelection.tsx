import React from 'react';
import styled from 'styled-components';
import { useFormContext, Control } from 'react-hook-form';
import { SmallHelper } from '../../../../components';
import {
  Condition,
  ConditionFactory,
  createFamilyCondition,
  createMultiOptionsAttributeCondition,
  createTextAttributeCondition,
  createCategoryCondition,
  Locale,
  LocaleCode,
  RuleDefinition,
} from '../../../../models/';
import { TextBoxBlue } from '../TextBoxBlue';
import { useProductsCount } from '../../hooks';
import { Router, Translate } from '../../../../dependenciesTools';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';
import { ConditionLine } from './ConditionLine';
import { ProductsCount } from '../ProductsCount';
import { AddConditionButton } from './AddConditionButton';
import { FormData } from '../../edit-rules.types';
import startImage from '../../../../assets/illustrations/start.svg';

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

const getValuesFromFormData = (getValues: Control['getValues']): FormData =>
  getValues({ nest: true });

const RuleProductSelectionFieldsetWithAction = styled.fieldset`
  background-image: url('${startImage}');
  padding-left: 12px;
  margin-left: -12px;
  background-repeat: no-repeat;
  padding-bottom: 20px;
`;

const RuleProductSelectionFieldset = styled.fieldset`
  padding-bottom: 20px;
`;

type Props = {
  currentCatalogLocale: LocaleCode;
  locales: Locale[];
  router: Router;
  ruleDefinition: RuleDefinition;
  scopes: IndexedScopes;
  translate: Translate;
};

const RuleProductSelection: React.FC<Props> = ({
  currentCatalogLocale,
  locales,
  router,
  ruleDefinition,
  scopes,
  translate,
}) => {
  const [conditions, setConditions] = React.useState<(Condition | null)[]>(
    ruleDefinition.conditions
  );

  const { getValues, unregister, watch } = useFormContext();
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
  const productsCount = useProductsCount(
    router,
    getValuesFromFormData(getValues)
  );
  const watchCondition = (conditionIdentifier: string) => {
    watch(conditionIdentifier);
  };

  React.useEffect(() => {
    setConditions(ruleDefinition.conditions);
  }, [ruleDefinition]);

  async function createCondition(fieldCode: string): Promise<Condition> {
    const factories: ConditionFactory[] = [
      createFamilyCondition,
      createTextAttributeCondition,
      createMultiOptionsAttributeCondition,
      createCategoryCondition,
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

  const isActiveConditionField: (fieldCode: string) => boolean = (
    fieldCode: string
  ) => {
    return conditions.some(condition => {
      return (
        condition !== null &&
        condition.hasOwnProperty('field') &&
        (condition as { field: string }).field === fieldCode
      );
    });
  };

  const Component = ruleDefinition.actions.length
    ? RuleProductSelectionFieldsetWithAction
    : RuleProductSelectionFieldset;
  return (
    <Component>
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
          <ProductsCount
            count={productsCount.value}
            status={productsCount.status}
            translate={translate}
          />
          <AddConditionContainer>
            <AddConditionButton
              router={router}
              handleAddCondition={handleAddCondition}
              translate={translate}
              isActiveConditionField={isActiveConditionField}
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
        <div className='AknGrid-body' data-testid={'condition-list'}>
          {conditions.map((condition, i) => {
            watchCondition(`content.conditions[${i}]`);
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
    </Component>
  );
};

RuleProductSelection.displayName = 'RuleProductSelection';

export { RuleProductSelection };
