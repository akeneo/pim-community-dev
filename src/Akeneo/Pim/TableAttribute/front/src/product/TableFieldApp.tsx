import React from 'react';
import { DependenciesProvider } from "@akeneo-pim-community/legacy-bridge";
import { ThemeProvider } from "styled-components";
import { Locale, pimTheme } from "akeneo-design-system";
import { TableInputValue } from "./TableInputValue";
import { TableValue } from "../models/TableValue";
import { TemplateContext } from "./table-field";

type TableFieldAppProps = TemplateContext & {
  valueData: TableValue;
  onChange: (tableValue: TableValue) => void;
  elements: { [position: string]: { [elementKey: string]: any } };
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
  valueData,
  onChange,
  elements,
}) => {
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
            {context.optional && context.removable && 'edit' === editMode &&
            <i className="AknIconButton AknIconButton--small icon-remove remove-attribute"
               data-attribute={attribute.code} data-toggle="tooltip"
               title={'pim_enrich.entity.product.module.attribute.remove_optional'}/>
            }
          </div>
          <div className="AknFieldContainer-inputContainer field-input">
            <TableInputValue valueData={valueData} tableConfiguration={attribute.table_configuration}
                             onChange={onChange}/>
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
