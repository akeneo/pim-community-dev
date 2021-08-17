import React from 'react';
import styled from 'styled-components';
import {Locale, uuid, Search, AkeneoThemedProps, getColor, Checkbox} from 'akeneo-design-system';
import {TableInputValue} from './TableInputValue';
import {TableRow, TableValue} from '../models/TableValue';
import {CopyContext, TemplateContext, Violations} from '../legacy/table-field';
import {ChannelCode, LocaleCode, useTranslate} from '@akeneo-pim-community/shared';
import {AddRowsButton} from './AddRowsButton';
import {ColumnCode, SelectOptionCode} from '../models/TableConfiguration';
import {clearCacheSelectOptions} from '../repositories/SelectOption';

const TableInputContainer = styled.div<{isCompareTranslate: boolean} & AkeneoThemedProps>`
  ${({isCompareTranslate}) => !isCompareTranslate && 'flex-basis: 100% !important'}
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
  elements: {[position: string]: {[elementKey: string]: any}};
  violations?: Violations[];
  copyContext?: CopyContext;
  onCopyCheckboxChange: any;
  copyCheckboxChecked?: boolean;
};

export type TableRowWithId = TableRow & {'unique id': string};
// As we can't have space, the 'unique id' can not be used as column
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
}) => {
  const translate = useTranslate();
  const addUniqueId = (value: TableValue) => {
    return value.map(row => {
      return Object.keys(row).reduce(
        (previousRow: TableRow & {'unique id': string}, columnCode) => {
          previousRow[columnCode] = row[columnCode];

          return previousRow;
        },
        {'unique id': uuid()}
      );
    });
  };

  const [tableValue, setTableValue] = React.useState<TableValueWithId>(addUniqueId(value.data || []));
  const [removedRows, setRemovedRows] = React.useState<{[key: string]: TableRowWithId}>({});
  const [searchText, setSearchText] = React.useState<string>('');
  const [copyChecked, setCopyChecked] = React.useState<boolean>(copyCheckboxChecked);
  const firstColumnCode: ColumnCode = attribute.table_configuration[0].code;
  const [violatedCellsById, setViolatedCellsById] = React.useState<ViolatedCell[]>([]);

  React.useEffect(() => {
    setViolatedCellsById(
      (violations || []).reduce((old, violation) => {
        if (locale === violation.locale && scope === violation.scope) {
          // Complete path looks like values[attributeCode-<all_channels>-en_US][3].ingredient
          const completePath = violation.path;
          const index = completePath.indexOf(']');
          if (index >= 0) {
            const realPath = completePath.substr(index + 1);
            const results = /^\[(\d+)\]\.(.+)$/.exec(realPath);
            if (results) {
              old.push({
                id: tableValue[parseInt(results[1])]['unique id'],
                columnCode: results[2],
              });
            }
          }
        }
        return old;
      }, [] as ViolatedCell[])
    );
  }, [JSON.stringify(violations)]);

  React.useEffect(() => {
    clearCacheSelectOptions();
  }, []);

  const renderElements: (position: string) => React.ReactNode = position => {
    return (
      <>
        {Object.keys(elements[position] || []).map(elementKey => {
          const element = elements[position][elementKey];
          if (typeof element.render === 'function') {
            return (
              <span key={elementKey} dangerouslySetInnerHTML={{__html: element.render().el.innerHTML as string}} />
            );
          } else if (typeof element === 'string') {
            return <span key={elementKey} dangerouslySetInnerHTML={{__html: element}} />;
          } else {
            return <span key={elementKey} dangerouslySetInnerHTML={{__html: element[0].outerHTML as string}} />;
          }
        })}
      </>
    );
  };

  const handleChange = (value: TableValueWithId) => {
    setTableValue(value);
    onChange(
      value.map(row => {
        return Object.keys(row)
          .filter(columnCode => columnCode !== 'unique id')
          .reduce((newRow: TableRow, columnCode) => {
            newRow[columnCode] = row[columnCode];
            return newRow;
          }, {});
      })
    );
  };

  const handleToggleRow = (optionCode: SelectOptionCode) => {
    const index = tableValue.findIndex(row => row[firstColumnCode] === optionCode);
    if (index >= 0) {
      const removed = tableValue.splice(index, 1);
      if (removed.length === 1) {
        removedRows[optionCode] = removed[0];
        setRemovedRows({...removedRows});
      }
    } else {
      if (typeof removedRows[optionCode] !== 'undefined') {
        tableValue.push(removedRows[optionCode]);
      } else {
        const newRow: TableRowWithId = {'unique id': uuid()};
        newRow[firstColumnCode] = optionCode;
        tableValue.push(newRow);
      }
    }
    handleChange([...tableValue]);
  };

  const handleCopyCheckedChange = (value: boolean) => {
    setCopyChecked(value);
    onCopyCheckboxChange(value);
  };

  const isCompareTranslate = typeof elements['comparison'] !== 'undefined';
  const isEditable = editMode === 'edit';

  const getLocaleScopeInfo = (locale: LocaleCode | null, scope: ChannelCode | null) => (
    <LocaleScopeInfo className='AknFieldContainer-fieldInfo field-info'>
      {(locale || scope) && (
        <span className='field-context'>
          {scope && <span className='field-scope'>{context.scopeLabel}&nbsp;</span>}
          {locale && <Locale code={locale} />}
        </span>
      )}
    </LocaleScopeInfo>
  );

  if (!copyContext && elements['comparison']) {
    renderElements('comparison');
    return null;
  }

  return (
    <>
      <TableInputContainer
        isCompareTranslate={isCompareTranslate}
        className={`${type} AknComparableFields-item AknFieldContainer original-field ${editMode}`}>
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
            {!copyContext && (
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
            {isEditable && (
              <AddRowsButton
                attribute={attribute}
                columnCode={firstColumnCode}
                checkedOptionCodes={tableValue.map(row => (row[firstColumnCode] ?? '') as string)}
                toggleChange={handleToggleRow}
              />
            )}
          </FieldInfo>
          {context.optional && context.removable && isEditable && (
            <i
              className='AknIconButton AknIconButton--small icon-remove remove-attribute'
              data-attribute={attribute.code}
              data-toggle='tooltip'
              title={'pim_enrich.entity.product.module.attribute.remove_optional'}
            />
          )}
        </TableFieldHeader>
        <div className='AknFieldContainer-inputContainer field-input'>
          <TableInputValue
            attribute={attribute}
            valueData={tableValue}
            tableConfiguration={attribute.table_configuration}
            onChange={handleChange}
            searchText={copyContext ? '' : searchText}
            readOnly={!isEditable}
            violatedCells={violatedCellsById}
            isCopying={!!copyContext}
          />
          {renderElements('field-input')}
        </div>
        <footer>
          <div className='AknFieldContainer-footer footer-elements-container'>{renderElements('footer')}</div>
        </footer>
      </TableInputContainer>
      <div className='AknComparableFields-item AknComparableFields-item--comparisonContainer AknFieldContainer comparison-elements-container'>
        {copyContext && (
          <div data-attribute={attribute.code} className='AknComparableFields field-container'>
            <div className='AknComparableFields-copyContainer copy-container'>
              <CopyCheckbox checked={copyChecked} onChange={handleCopyCheckedChange} data-testid='copyCheckbox' />
              <div className='AknFieldContainer AknComparableFields-item'>
                <TableFieldHeader className='AknFieldContainer-header' isCompareTranslate={true}>
                  <TableFieldLabel className='AknFieldContainer-label'>{label}</TableFieldLabel>
                  {getLocaleScopeInfo(copyContext.locale, copyContext.scope)}
                </TableFieldHeader>
                <div className='AknFieldContainer-inputContainer field-input'>
                  <TableInputValue
                    attribute={attribute}
                    valueData={addUniqueId(copyContext.data || [])}
                    tableConfiguration={attribute.table_configuration}
                    readOnly={true}
                    isCopying={!!copyContext}
                  />
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  );
};

export {TableFieldApp};
