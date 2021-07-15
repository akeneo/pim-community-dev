import React from 'react';
import styled from 'styled-components';
import {Locale, uuid, Search, AkeneoThemedProps, getColor} from 'akeneo-design-system';
import {TableInputValue} from './TableInputValue';
import {TableRow, TableValue} from '../models/TableValue';
import {TemplateContext, Violations} from '../legacy/table-field';
import {useTranslate} from '@akeneo-pim-community/shared';
import {AddRowsButton} from './AddRowsButton';
import {ColumnCode, SelectOptionCode} from '../models/TableConfiguration';

const TableInputContainer = styled.div<{isCompareTranslate: boolean} & AkeneoThemedProps>`
  ${({isCompareTranslate}) => !isCompareTranslate && 'flex-basis: 100% !important'}
`;

const FieldInfo = styled.div`
  display: flex;
  height: 24px;
  line-height: 24px;
  & > *:not(:last-child) {
    border-right: 1px solid ${getColor('grey', 100)};
    padding-right: 20px;
  }
  & > * {
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

type TableFieldAppProps = TemplateContext & {
  onChange: (tableValue: TableValue) => void;
  elements: {[position: string]: {[elementKey: string]: any}};
  violations?: Violations[];
  copyContext?: TemplateContext;
};

type TableRowWithId = TableRow & {'unique id': string};
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
  violations = [],
}) => {
  const translate = useTranslate();

  const [tableValue, setTableValue] = React.useState<TableValueWithId>(
    (value.data || []).map(row => {
      return Object.keys(row).reduce(
        (previousRow: TableRow & {'unique id': string}, columnCode) => {
          previousRow[columnCode] = row[columnCode];

          return previousRow;
        },
        {'unique id': uuid()}
      );
    })
  );
  const [removedRows, setRemovedRows] = React.useState<{[key: string]: TableRowWithId}>({});
  const [searchText, setSearchText] = React.useState<string>('');
  const firstColumnCode: ColumnCode = attribute.table_configuration[0].code;
  const [violatedCellsById] = React.useState<ViolatedCell[]>(
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

  const renderElements: (position: string) => React.ReactNode = position => {
    return (
      <>
        {Object.keys(elements[position] || []).map(elementKey => {
          const element = elements[position][elementKey];
          if (typeof element.render === 'function') {
            return (
              <span key={elementKey} dangerouslySetInnerHTML={{__html: element.render().el.innerHTML as string}} />
            );
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

  const isCompareTranslate = typeof elements['comparison'] !== 'undefined';

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
            <LocaleScopeInfo className='AknFieldContainer-fieldInfo field-info'>
              {(locale || scope) && (
                <span className='field-context'>
                  {scope && <span className='field-scope'>{context.scopeLabel}&nbsp;</span>}
                  {locale && <Locale code={locale} />}
                </span>
              )}
            </LocaleScopeInfo>
            <AddRowsButton
              attributeCode={attribute.code}
              columnCode={firstColumnCode}
              checkedOptionCodes={tableValue.map(row => (row[firstColumnCode] ?? '') as string)}
              toggleChange={handleToggleRow}
            />
          </FieldInfo>
          {context.optional && context.removable && 'edit' === editMode && (
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
              attributeCode={attribute.code}
              valueData={tableValue}
              tableConfiguration={attribute.table_configuration}
              onChange={handleChange}
              searchText={copyContext ? '' : searchText}
              violatedCells={violatedCellsById}
            />
            {renderElements('field-input')}
          </div>
          <footer>
            <div className='AknFieldContainer-footer footer-elements-container'>{renderElements('footer')}</div>
          </footer>
        </TableInputContainer>
        <div className='AknComparableFields-item AknComparableFields-item--comparisonContainer AknFieldContainer comparison-elements-container'>
          {copyContext && (
            <TableInputValue
              attributeCode={attribute.code}
              valueData={tableValue}
              tableConfiguration={attribute.table_configuration}
              onChange={handleChange}
              searchText={copyContext ? '' : searchText}
            />
          )}
          {!copyContext && renderElements('comparison')}
        </div>
      </>
  );
};

export {TableFieldApp};
