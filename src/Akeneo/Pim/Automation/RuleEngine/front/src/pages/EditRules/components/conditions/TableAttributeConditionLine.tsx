import React from 'react';
import {Controller, useFormContext} from 'react-hook-form';
import {TableAttributeCondition} from '../../../../models/conditions';
import {ConditionLineProps} from './ConditionLineProps';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import {Attribute, Locale} from '../../../../models';
import {getAttributeByIdentifier} from '../../../../repositories/AttributeRepository';
import {
  PendingBackendTableFilterValue,
  TableAttribute,
  TableAttributeConditionLineInput,
} from '@akeneo-pim-ge/table_attribute';
import {
  ConditionLineErrorsContainer,
  ConditionLineFormAndErrorsContainer,
  ConditionLineFormContainer,
  FieldColumn,
  LocaleColumn,
  ScopeColumn,
} from './style';
import {
  getScopeValidation,
  ScopeSelector,
} from '../../../../components/Selectors/ScopeSelector';
import {
  getLocaleValidation,
  LocaleSelector,
} from '../../../../components/Selectors/LocaleSelector';
import {LineErrors} from '../LineErrors';
import {useControlledFormInputCondition} from '../../hooks';
import styled from 'styled-components';

const TableAttributeConditionLineInputContainer = styled.div`
  min-width: 480px;
  display: inline-block;
  margin: 0 20px 0 0;
  height: 40px;
`;

type TableAttributeConditionLineProps = ConditionLineProps & {
  condition: TableAttributeCondition;
};

const TableAttributeConditionLine: React.FC<TableAttributeConditionLineProps> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const field = condition.field;
  const operator = condition.operator;
  const value = condition.value.value;
  const column = condition.value.column;
  const row = condition.value.row;

  const [attribute, setAttribute] = React.useState<Attribute | null>();
  React.useEffect(() => {
    getAttributeByIdentifier(condition.field, router).then(attribute =>
      setAttribute(attribute)
    );
  }, []);
  const title =
    attribute && attribute.labels[currentCatalogLocale]
      ? attribute.labels[currentCatalogLocale]
      : `[${field}]`;

  const {
    fieldFormName,
    localeFormName,
    scopeFormName,
    getLocaleFormValue,
    getScopeFormValue,
    isFormFieldInError,
    formName,
  } = useControlledFormInputCondition<string[]>(lineNumber);

  const getAvailableLocales = (): Locale[] => {
    if (!attribute || !attribute.scopable) {
      return locales;
    }
    const scopeCode = getScopeFormValue();
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].locales;
    }
    return [];
  };

  const {watch, getValues, setValue} = useFormContext();
  watch(formName('operator'));
  watch(formName('value'));
  watch(formName('value.column'));
  watch(formName('value.row'));
  watch(formName('value.value'));

  const handleTableInputChange = (value: PendingBackendTableFilterValue) => {
    setValue(formName('operator'), value.operator);
    setValue(formName('value.value'), value.value);
    setValue(formName('value.column'), value.column);
    setValue(formName('value.row'), value.row);
  };

  return !attribute ? (
    <></>
  ) : (
    <ConditionLineFormAndErrorsContainer className={'AknGrid-bodyCell'}>
      <ConditionLineFormContainer>
        <FieldColumn className={'AknGrid-bodyCell--highlight'} title={title}>
          {title}
        </FieldColumn>
        <Controller
          as={<input type='hidden' />}
          name={fieldFormName}
          defaultValue={field}
        />
        <Controller
          as={<input type='hidden' />}
          name={formName('operator')}
          defaultValue={operator}
          rules={{
            required: translate(
              'pimee_catalog_rule.exceptions.required_operator_for_operation'
            ),
          }}
        />
        <Controller
          as={<input type='hidden' />}
          name={formName('value.value')}
          defaultValue={value}
          rules={{required: false}}
        />
        <Controller
          as={<input type='hidden' />}
          name={formName('value.column')}
          defaultValue={column}
          rules={{
            required: translate('pimee_catalog_rule.exceptions.required_item'),
          }}
        />
        <Controller
          as={<input type='hidden' />}
          name={formName('value.row')}
          defaultValue={row}
        />
        <TableAttributeConditionLineInputContainer>
          <TableAttributeConditionLineInput
            attribute={(attribute || undefined) as TableAttribute | undefined}
            value={{
              operator: getValues('value.column') || operator,
              value: getValues('value.value') || value,
              row: getValues('value.row') || row,
              column: getValues('value.column') || column,
            }}
            onChange={handleTableInputChange}
          />
        </TableAttributeConditionLineInputContainer>
        {(attribute.scopable || getScopeFormValue()) && (
          <ScopeColumn
            className={
              isFormFieldInError('scope') ? 'select2-container-error' : ''
            }>
            <Controller
              allowClear={!attribute.scopable}
              as={ScopeSelector}
              availableScopes={Object.values(scopes)}
              currentCatalogLocale={currentCatalogLocale}
              data-testid={`edit-rules-input-${lineNumber}-scope`}
              hiddenLabel
              name={scopeFormName}
              defaultValue={getScopeFormValue()}
              value={getScopeFormValue()}
              rules={getScopeValidation(
                attribute || null,
                scopes,
                translate,
                currentCatalogLocale
              )}
            />
          </ScopeColumn>
        )}
        {(attribute.localizable || getLocaleFormValue()) && (
          <LocaleColumn
            className={
              isFormFieldInError('locale') ? 'select2-container-error' : ''
            }>
            <Controller
              as={LocaleSelector}
              data-testid={`edit-rules-input-${lineNumber}-locale`}
              hiddenLabel
              availableLocales={getAvailableLocales()}
              defaultValue={getLocaleFormValue()}
              value={getLocaleFormValue()}
              allowClear={!attribute.localizable}
              name={localeFormName}
              rules={getLocaleValidation(
                attribute || null,
                locales,
                getAvailableLocales(),
                getScopeFormValue(),
                translate,
                currentCatalogLocale
              )}
            />
          </LocaleColumn>
        )}
      </ConditionLineFormContainer>
      <ConditionLineErrorsContainer>
        <LineErrors lineNumber={lineNumber} type='conditions' />
      </ConditionLineErrorsContainer>
    </ConditionLineFormAndErrorsContainer>
  );
};

export {TableAttributeConditionLine, TableAttributeConditionLineProps};
