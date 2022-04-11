import React from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, Checkbox, getColor, Locale, Search} from 'akeneo-design-system';
import {TableInputValue} from './TableInputValue';
import {ColumnCode, TableAttribute, TableRow, TableValue} from '../models';
import {CopyContext, TemplateContext, Violations} from '../legacy/table-field';
import {ChannelCode, LocaleCode, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AddRowsButton} from './AddRowsButton';
import {ProductFieldElement, useRenderElements} from './useRenderElements';
import {UNIQUE_ID_KEY, useUniqueIds} from './useUniqueIds';
import {useToggleRow} from './useToggleRow';
import {ReferenceEntityRecordRepository, SelectOptionRepository} from '../repositories';
import {AttributeContext, LocaleCodeContext} from '../contexts';

const TableInputContainer = styled.div<{isCompareTranslate: boolean} & AkeneoThemedProps>`
  ${({isCompareTranslate}) =>
    !isCompareTranslate &&
    css`
      width: 100%;
      flex-basis: 100% !important;
    `}
`;

const FieldInfo = styled.div`
  display: flex;
  height: 24px;
  line-height: 24px;
  & > *:not(:last-child):not(:empty) {
    border-right: 1px solid ${getColor('grey', 100)};
    padding-right: 20px;
  }
  & > *:not(:empty) {
    padding-left: 20px;
  }
`;

const TableFieldSearch = styled(Search)`
  height: 24px;
  border-bottom: none;
`;

const TableFieldHeader = styled.div<{isCompareTranslate: boolean} & AkeneoThemedProps>`
  ${({isCompareTranslate}) => isCompareTranslate && 'width: 460px;'}
`;

const TableFieldLabel = styled.label`
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: inline-block;
`;

const LocaleScopeInfo = styled.span`
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: inline-block;
`;

const CopyCheckbox = styled(Checkbox)`
  margin-right: 5px;
`;

type TableFieldAppProps = TemplateContext & {
  onChange: (tableValue: TableValue) => void;
  elements: {[position: string]: {[elementKey: string]: ProductFieldElement}};
  violations?: Violations[];
  copyContext?: CopyContext;
  onCopyCheckboxChange: any;
  copyCheckboxChecked?: boolean;
  isDisplayedForCurrentLocale?: boolean;
};

export type TableRowWithId = TableRow & {[UNIQUE_ID_KEY]: string};
export type TableValueWithId = TableRowWithId[];

export type ViolatedCell = {
  id: string;
  columnCode: ColumnCode;
};

const TableFieldApp: React.FC<TableFieldAppProps> = ({
  type,
  editMode,
  fieldId,
  label,
  locale,
  scope,
  context,
  attribute,
  value,
  onChange,
  copyContext,
  elements,
  onCopyCheckboxChange,
  copyCheckboxChecked = false,
  violations = [],
  isDisplayedForCurrentLocale = true,
}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const {addUniqueIds, removeUniqueIds} = useUniqueIds();
  const [tableValue, setTableValue] = React.useState<TableValueWithId>(addUniqueIds(value?.data || []));
  const [searchText, setSearchText] = React.useState<string>('');
  const [copyChecked, setCopyChecked] = React.useState<boolean>(copyCheckboxChecked);
  const firstColumnCode: ColumnCode = attribute.table_configuration[0].code;
  const [attributeState, setAttributeState] = React.useState<TableAttribute>(attribute);

  const handleChange = (value: TableValueWithId) => {
    setTableValue(value);
    onChange(removeUniqueIds(value));
  };

  const handleToggleRow = useToggleRow(tableValue, firstColumnCode, handleChange);

  const violatedCellsById = (violations || []).reduce((old, violation) => {
    if (locale === violation.locale && scope === violation.scope) {
      // Complete path looks like values[attributeCode-<all_channels>-en_US][3].ingredient
      const completePath = violation.path;
      const index = completePath.indexOf(']');
      if (index >= 0) {
        const realPath = completePath.substr(index + 1);
        const results = /^\[(\d+)\]\.(.+)$/.exec(realPath);
        if (results) {
          old.push({
            id: tableValue[parseInt(results[1])][UNIQUE_ID_KEY],
            columnCode: results[2],
          });
        }
      }
    }
    return old;
  }, [] as ViolatedCell[]);

  React.useEffect(() => {
    SelectOptionRepository.clearCache();
  }, []);

  React.useEffect(() => {
    // clear cache on unmount
    return () => {
      ReferenceEntityRecordRepository.clearCache();
    };
  }, []);

  const renderElements = useRenderElements(attributeState.code, elements);

  const handleCopyCheckedChange = (value: boolean) => {
    setCopyChecked(value);
    onCopyCheckboxChange(value);
  };

  const isCompareTranslate = typeof elements['comparison'] !== 'undefined';
  const isEditable = editMode === 'edit';

  const getLocaleScopeInfo = (locale: LocaleCode | null, scope: ChannelCode | null) =>
    (locale || scope) && (
      <LocaleScopeInfo className='AknFieldContainer-fieldInfo field-info'>
        <span className='field-context'>
          {scope && <span className='field-scope'>{context.scopeLabel}&nbsp;</span>}
          {locale && <Locale code={locale} />}
        </span>
      </LocaleScopeInfo>
    );

  if (!copyContext && elements['comparison']) {
    renderElements('comparison');
    return null;
  }

  return (
    <AttributeContext.Provider value={{attribute: attributeState, setAttribute: setAttributeState}}>
      <TableInputContainer
        isCompareTranslate={isCompareTranslate}
        className={`${type} AknComparableFields-item AknFieldContainer original-field ${editMode}`}
      >
        <TableFieldHeader className='AknFieldContainer-header' isCompareTranslate={isCompareTranslate}>
          <TableFieldLabel className='AknFieldContainer-label' htmlFor={fieldId}>
            <span className='AknFieldContainer-labelAnnotation badge-elements-container'>
              {renderElements('badge')}
            </span>
            {label}
            <span className='AknFieldContainer-labelAnnotation label-elements-container'>
              {renderElements('label')}
            </span>
          </TableFieldLabel>
          <FieldInfo>
            {!copyContext && isDisplayedForCurrentLocale && (
              <div>
                <TableFieldSearch
                  onSearchChange={setSearchText}
                  placeholder={translate('pim_common.search')}
                  searchValue={searchText}
                  title={translate('pim_common.search')}
                />
              </div>
            )}
            {getLocaleScopeInfo(locale, scope)}
            {isEditable && isDisplayedForCurrentLocale && (
              <AddRowsButton
                checkedOptionCodes={tableValue.map(row => (row[firstColumnCode] ?? '') as string)}
                toggleChange={handleToggleRow}
              />
            )}
          </FieldInfo>
          {context.optional && context.removable && isEditable && (
            <i
              className='AknIconButton AknIconButton--small icon-remove remove-attribute'
              data-attribute={attributeState.code}
              data-toggle='tooltip'
              title={translate('pim_enrich.entity.product.module.attribute.remove_optional')}
            />
          )}
        </TableFieldHeader>
        <div className='AknFieldContainer-inputContainer field-input'>
          {isDisplayedForCurrentLocale && (
            <LocaleCodeContext.Provider value={{localeCode: catalogLocale}}>
              <TableInputValue
                valueData={tableValue}
                onChange={handleChange}
                searchText={copyContext ? '' : searchText}
                visibility={isEditable ? 'CAN_EDIT' : 'CANNOT_EDIT'}
                violatedCells={violatedCellsById}
                isCopying={!!copyContext}
              />
            </LocaleCodeContext.Provider>
          )}
          {renderElements('field-input')}
        </div>
        <footer>
          <div className='AknFieldContainer-footer footer-elements-container'>{renderElements('footer')}</div>
        </footer>
      </TableInputContainer>
      <div className='AknComparableFields-item AknComparableFields-item--comparisonContainer AknFieldContainer comparison-elements-container'>
        {copyContext && (
          <div data-attribute={attributeState.code} className='AknComparableFields field-container'>
            <div className='AknComparableFields-copyContainer copy-container'>
              <CopyCheckbox checked={copyChecked} onChange={handleCopyCheckedChange} data-testid='copyCheckbox' />
              <div className='AknFieldContainer AknComparableFields-item'>
                <TableFieldHeader className='AknFieldContainer-header' isCompareTranslate={true}>
                  <TableFieldLabel className='AknFieldContainer-label'>{label}</TableFieldLabel>
                  {getLocaleScopeInfo(copyContext.locale, copyContext.scope)}
                </TableFieldHeader>
                <div className='AknFieldContainer-inputContainer field-input'>
                  <LocaleCodeContext.Provider value={{localeCode: copyContext?.locale ?? catalogLocale}}>
                    <TableInputValue
                      valueData={addUniqueIds(copyContext.data || [])}
                      visibility={'READ_ONLY'}
                      isCopying={!!copyContext}
                    />
                  </LocaleCodeContext.Provider>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </AttributeContext.Provider>
  );
};

export {TableFieldApp};
