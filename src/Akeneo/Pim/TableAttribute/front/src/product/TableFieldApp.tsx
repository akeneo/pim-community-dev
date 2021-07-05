import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import styled, {ThemeProvider} from 'styled-components';
import {Button, Locale, pimTheme, uuid, Search} from 'akeneo-design-system';
import {TableInputValue} from './TableInputValue';
import {TableRow, TableValue} from '../models/TableValue';
import {TemplateContext} from '../legacy/table-field';
import {useTranslate} from '@akeneo-pim-community/shared';

const TableInputContainer = styled.div`
  flex-basis: 100% !important;
`;

type TableFieldAppProps = TemplateContext & {
  onChange: (tableValue: TableValue) => void;
  elements: {[position: string]: {[elementKey: string]: any}};
};

// As we can't have space, the 'unique id' can not be used as column
export type TableValueWithId = (TableRow & {'unique id': string})[];

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
  elements,
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
  const [searchText, setSearchText] = React.useState<string>('');

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

  const addFakeRow = () => {
    const newValue = [...tableValue];
    newValue.push({'unique id': uuid()});
    handleChange([...newValue]);
  };

  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <TableInputContainer
          className={`${type} AknComparableFields-item AknFieldContainer original-field ${editMode}`}>
          <div className='AknFieldContainer-header'>
            <label className='AknFieldContainer-label' htmlFor={fieldId}>
              <span className='AknFieldContainer-labelAnnotation badge-elements-container'>
                {renderElements('badge')}
              </span>
              {label}
              <span className='AknFieldContainer-labelAnnotation label-elements-container'>
                {renderElements('label')}
              </span>
            </label>
            <span className='AknFieldContainer-fieldInfo field-info'>
              {(locale || scope) && (
                <span className='field-context'>
                  {scope && <span className='field-scope'>{context.scopeLabel}&nbsp;</span>}
                  {locale && <Locale code={locale} />}
                </span>
              )}
            </span>
            <Search
              onSearchChange={setSearchText}
              placeholder={translate('pim_common.search')}
              searchValue={searchText}
              title={translate('pim_common.search')}
            />
            <Button
              size='small'
              onClick={() => {
                addFakeRow();
              }}>
              Add row
            </Button>
            {context.optional && context.removable && 'edit' === editMode && (
              <i
                className='AknIconButton AknIconButton--small icon-remove remove-attribute'
                data-attribute={attribute.code}
                data-toggle='tooltip'
                title={'pim_enrich.entity.product.module.attribute.remove_optional'}
              />
            )}
          </div>
          <div className='AknFieldContainer-inputContainer field-input'>
            <TableInputValue
              valueData={tableValue}
              tableConfiguration={attribute.table_configuration}
              onChange={handleChange}
              searchText={searchText}
            />
            {renderElements('field-input')}
          </div>
          <footer>
            <div className='AknFieldContainer-footer footer-elements-container'>{renderElements('footer')}</div>
          </footer>
        </TableInputContainer>
        <div className='AknComparableFields-item AknComparableFields-item--comparisonContainer AknFieldContainer comparison-elements-container'>
          {renderElements('comparison')}
        </div>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {TableFieldApp};
