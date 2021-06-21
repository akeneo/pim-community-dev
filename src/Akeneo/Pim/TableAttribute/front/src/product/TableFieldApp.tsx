import React from 'react';
import { DependenciesProvider } from "@akeneo-pim-community/legacy-bridge";
import { ThemeProvider } from "styled-components";
import { Button, Locale, pimTheme, TextInput, uuid } from "akeneo-design-system";
import { TableInputValue } from "./TableInputValue";
import { TableRow, TableValue } from "../models/TableValue";
import { TemplateContext } from "./table-field";

type TableFieldAppProps = TemplateContext & {
  valueData: TableValue;
  onChange: (tableValue: TableValue) => void;
  elements: { [position: string]: { [elementKey: string]: any } };
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
  valueData,
  onChange,
  elements,
}) => {
  const [tableValue, setTableValue] = React.useState<TableValueWithId>(valueData.map(row => {
    return Object.keys(row).reduce((previousRow, columnCode) => {
      previousRow[columnCode] = row[columnCode];

      return previousRow;
    }, {'unique id': uuid()});
  });
  const [searchText, setSearchText] = React.useState<string>('');

  const renderElements: (position: string) => React.ReactNode = (position) => {
    return <>
      {Object.keys(elements[position] || []).map(elementKey => {
        const element = elements[position][elementKey];
        if (typeof element.render === 'function') {
          return <span key={elementKey} dangerouslySetInnerHTML={{__html: element.render().el.innerHTML as string}}/>;
        } else {
          // TODO Check this one
          return <span key={elementKey} dangerouslySetInnerHTML={{__html: element as string}}/>;
        }
      })}
    </>;
  }

  const handleChange = (value: TableValueWithId) => {
    setTableValue(value);
    onChange(value.map(row => {
      return Object.keys(row).filter(columnCode => columnCode !== 'unique id').reduce((newRow, columnCode) => {
        newRow[columnCode] = row[columnCode];
        return newRow;
      }, {});
    }));
  }

  const addFakeRow = () => {
    const newValue = [...tableValue];
    newValue.push({'unique id': uuid()});
    handleChange([...newValue]);
  }

  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <div className={`${type} AknComparableFields-item AknFieldContainer original-field ${editMode}`}>
          <div className="AknFieldContainer-header">
            <label className="AknFieldContainer-label" htmlFor={fieldId}>
              <span className="AknFieldContainer-labelAnnotation badge-elements-container">
                {renderElements('badge')}
              </span>
              {label}
              <span className="AknFieldContainer-labelAnnotation label-elements-container">
                {renderElements('label')}
              </span>
            </label>
            <span className="AknFieldContainer-fieldInfo field-info">
                  {(locale || scope) &&
                  <span className="field-context">
                      {scope && <span className="field-scope">{context.scopeLabel}&nbsp;</span>}
                    {locale && <Locale code={locale}/>}
                    </span>
                  }
                </span>
            <Button size="small" onClick={() => {addFakeRow()}}>Add row</Button>
            <TextInput value={searchText} onChange={setSearchText}/>
            {context.optional && context.removable && 'edit' === editMode &&
            <i className="AknIconButton AknIconButton--small icon-remove remove-attribute"
               data-attribute={attribute.code} data-toggle="tooltip"
               title={'pim_enrich.entity.product.module.attribute.remove_optional'}/>
            }
          </div>
          <div className="AknFieldContainer-inputContainer field-input">
            <TableInputValue
              valueData={tableValue}
              tableConfiguration={attribute.table_configuration}
              onChange={handleChange}
              searchText={searchText}
            />
            {renderElements('field-input')}
          </div>
          <footer>
            <div className="AknFieldContainer-footer footer-elements-container">
              {renderElements('footer')}
            </div>
          </footer>
        </div>
        <div
          className="AknComparableFields-item AknComparableFields-item--comparisonContainer AknFieldContainer comparison-elements-container">
          {renderElements('comparison')}
        </div>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export { TableFieldApp };
