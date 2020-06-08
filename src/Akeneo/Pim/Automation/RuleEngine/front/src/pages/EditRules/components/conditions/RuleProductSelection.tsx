import React from 'react';
import styled from 'styled-components';
import { useFormContext, Control, useFieldArray } from 'react-hook-form';
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
} from '../../../../models/';
import { TextBoxBlue } from '../TextBoxBlue';
import { useProductsCount } from '../../hooks';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';
import { ConditionLine } from './ConditionLine';
import { ProductsCount } from '../ProductsCount';
import { AddConditionButton } from './AddConditionButton';
import { FormData } from '../../edit-rules.types';
import startImage from '../../../../assets/illustrations/start.svg';
import { useBackboneRouter, useTranslate } from "../../../../dependenciesTools/hooks";

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
  scopes: IndexedScopes;
};

const RuleProductSelection: React.FC<Props> = ({
  currentCatalogLocale,
  locales,
  scopes,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();

  const { getValues, control } = useFormContext();
  const { fields, append, remove } = useFieldArray({ control, name: 'content.conditions' });

  const productsCount = useProductsCount(
    router,
    getValuesFromFormData(getValues)
  );

  const createCondition: ((fieldCode: string) => Promise<Condition>) = async (fieldCode) => {
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
    createCondition(fieldCode).then(condition => append(condition));
  };

  const isActiveConditionField = React.useCallback((fieldCode: string) => {
    return (getValues({ nest: true })?.content?.conditions || []).some((condition: Condition) => {
      return (
        condition.hasOwnProperty('field') &&
        (condition as { field: string }).field === fieldCode
      );
    });
  }, [getValues({ nest: true })?.content?.conditions ]);

  const hasActions: () => boolean = () => {
    return (getValues({ nest: true })?.content?.actions || []).length > 0;
  }

  const Component = hasActions()
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
          />
          <AddConditionContainer>
            <AddConditionButton
              handleAddCondition={handleAddCondition}
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
          {fields.map((field, i) => {
            return (
              <ConditionLine
                condition={field as Condition}
                lineNumber={i}
                key={field.id}
                locales={locales}
                scopes={scopes}
                currentCatalogLocale={currentCatalogLocale}
                deleteCondition={remove}
              />
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
