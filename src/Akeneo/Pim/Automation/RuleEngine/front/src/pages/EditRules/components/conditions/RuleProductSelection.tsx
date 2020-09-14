import React from 'react';
import styled from 'styled-components';
import { Control, useFormContext } from 'react-hook-form';
import { SmallHelper } from '../../../../components';
import {
  Condition,
  ConditionFactory,
  createBooleanAttributeCondition,
  createCategoryCondition,
  createCompletenessCondition,
  createFamilyCondition,
  createGroupsCondition,
  createIdentifierCondition,
  createIdentifierAttributeCondition,
  createMeasurementAttributeCondition,
  createNumberAttributeCondition,
  createSimpleMultiOptionsAttributeCondition,
  createStatusCondition,
  createTextAttributeCondition,
  Locale,
  LocaleCode,
  createDateAttributeCondition,
  createDateSystemCondition,
  createTextareaAttributeCondition,
  createAssetCollectionAttributeCondition,
  createSimpleMultiReferenceEntitiesAttributeCondition,
  createPriceCollectionAttributeCondition,
  AttributeType,
  createPictureAttributeCondition,
  createFileAttributeCondition,
} from '../../../../models/';
import { TextBoxBlue } from '../TextBoxBlue';
import { useProductsCount } from '../../hooks';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';
import { ConditionLine } from './ConditionLine';
import { ProductsCount } from '../ProductsCount';
import { AddFieldButton } from '../../../../components/Selectors/AddFieldButton';
import { FormData } from '../../edit-rules.types';
import startImage from '../../../../assets/illustrations/start.svg';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { Action } from '../../../../models/Action';
import { EmptySectionMessage } from '../EmptySectionMessage';

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

const RuleProductSelectionFieldset = styled.fieldset<{ hasActions: boolean }>`
  padding-bottom: 20px;
  &:focus {
    outline: none;
  }

  ${({ hasActions }) =>
    hasActions &&
    `
    background-image: url('${startImage}');
    padding-left: 12px;
    margin-left: -12px;
    background-repeat: no-repeat;
  `}
`;

const getValuesFromFormData = (getValues: Control['getValues']): FormData =>
  getValues({ nest: true });

type Props = {
  currentCatalogLocale: LocaleCode;
  locales: Locale[];
  scopes: IndexedScopes;
  conditions: (Condition | null)[];
  handleAddCondition: (condition: Condition) => void;
  handleDeleteCondition: (lineNumber: number) => void;
};

const RuleProductSelection: React.FC<Props> = ({
  currentCatalogLocale,
  locales,
  scopes,
  conditions,
  handleAddCondition,
  handleDeleteCondition,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();

  const { getValues } = useFormContext();

  const productsCount = useProductsCount(
    router,
    getValuesFromFormData(getValues)
  );

  const createCondition: (
    fieldCode: string
  ) => Promise<Condition> = async fieldCode => {
    const factories: ConditionFactory[] = [
      // System
      createCategoryCondition,
      createCompletenessCondition,
      createDateSystemCondition,
      createFamilyCondition,
      createIdentifierCondition,
      createGroupsCondition,
      createStatusCondition,
      // Attributes
      createAssetCollectionAttributeCondition,
      createBooleanAttributeCondition,
      createDateAttributeCondition,
      createFileAttributeCondition,
      createIdentifierAttributeCondition,
      createMeasurementAttributeCondition,
      createNumberAttributeCondition,
      createPictureAttributeCondition,
      createSimpleMultiOptionsAttributeCondition,
      createSimpleMultiReferenceEntitiesAttributeCondition,
      createTextAttributeCondition,
      createTextareaAttributeCondition,
      createPriceCollectionAttributeCondition,
    ];

    for (let i = 0; i < factories.length; i++) {
      const factory = factories[i];
      const condition = await factory(fieldCode, router);
      if (condition !== null) {
        return condition;
      }
    }

    throw new Error(`Unknown factory for field ${fieldCode}`);
  };

  const addCondition = (fieldCode: string) => {
    createCondition(fieldCode).then(condition => handleAddCondition(condition));
  };

  // Add here the fields handled by the rule conditions.
  // Be sure that the associated UI component exists to display it correctly.
  const SYSTEM_FIELDS = [
    'categories',
    'family',
    'groups',
    'enabled',
    'identifier',
    'completeness',
    'created',
    'updated',
  ];

  const ATTRIBUTE_TYPES = [
    AttributeType.ASSET_COLLECTION,
    AttributeType.BOOLEAN,
    AttributeType.DATE,
    AttributeType.FILE,
    AttributeType.IDENTIFIER,
    AttributeType.IMAGE,
    AttributeType.METRIC,
    AttributeType.NUMBER,
    AttributeType.OPTION_MULTI_SELECT,
    AttributeType.OPTION_SIMPLE_SELECT,
    AttributeType.PRICE_COLLECTION,
    AttributeType.REFERENCE_ENTITY_COLLECTION,
    AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
    AttributeType.TEXT,
    AttributeType.TEXTAREA,
  ];

  const isActiveConditionField = React.useCallback(
    (fieldCode: string) => {
      return (getValues({ nest: true })?.content?.conditions || []).some(
        (condition: Condition) => {
          return (
            Object.hasOwnProperty.call(condition, 'field') &&
            (condition as { field: string }).field === fieldCode
          );
        }
      );
    },
    [getValues({ nest: true })?.content?.conditions]
  );

  const hasActions =
    (getValues({ nest: true })?.content?.actions || []).filter(
      (action: Action) => action !== null
    ).length > 0;

  return (
    <RuleProductSelectionFieldset hasActions={hasActions}>
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
            <AddFieldButton
              handleAddField={addCondition}
              isFieldAlreadySelected={isActiveConditionField}
              filterSystemFields={SYSTEM_FIELDS}
              filterAttributeTypes={ATTRIBUTE_TYPES}
              containerCssClass={'add-conditions-button'}
              dropdownCssClass={'add-conditions-dropdown'}
              placeholder={translate(
                'pimee_catalog_rule.form.edit.add_conditions'
              )}
              id={'add_conditions'}
            />
          </AddConditionContainer>
        </HeaderPartContainer>
      </Header>
      <SmallHelper>
        {translate('pimee_catalog_rule.form.helper.product_selection')}
        &nbsp;
        <a
          href='https://help.akeneo.com/pim/serenity/articles/manage-your-rules.html#product-selection'
          target='_blank'
          rel='noopener noreferrer'>
          {translate(
            'pimee_catalog_rule.form.helper.product_selection_doc_link'
          )}
        </a>
      </SmallHelper>
      <div className='AknGrid AknGrid--unclickable'>
        <div className='AknGrid-body' data-testid={'condition-list'}>
          {conditions.map((condition, i) => {
            return (
              condition && (
                <ConditionLine
                  condition={condition}
                  lineNumber={i}
                  key={i}
                  locales={locales}
                  scopes={scopes}
                  currentCatalogLocale={currentCatalogLocale}
                  deleteCondition={() => {
                    handleDeleteCondition(i);
                  }}
                />
              )
            );
          })}
        </div>
      </div>
      <LegendSrOnly>
        {translate('pimee_catalog_rule.form.legend.product_selection')}
      </LegendSrOnly>
      {!conditions.filter(Boolean).length && (
        <EmptySectionMessage>
          <div>
            {translate(
              'pimee_catalog_rule.form.edit.empty_section.set_up_condition'
            )}
            <br />
            {translate('pimee_catalog_rule.form.edit.empty_section.add_action')}
          </div>
        </EmptySectionMessage>
      )}
    </RuleProductSelectionFieldset>
  );
};

RuleProductSelection.displayName = 'RuleProductSelection';

export { RuleProductSelection };
