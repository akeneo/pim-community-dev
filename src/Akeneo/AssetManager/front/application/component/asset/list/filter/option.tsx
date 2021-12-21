import React, {memo, useRef, useState} from 'react';
import {useTranslate, getLabel} from '@akeneo-pim-community/shared';
import {FilterView, FilterViewProps} from 'akeneoassetmanager/application/configuration/value';
import {isOptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {Option, getOptionLabel} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
import {isOptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import {getAttributeFilterKey} from 'akeneoassetmanager/tools/filter';
import OptionCode from 'akeneoassetmanager/domain/model/attribute/type/option/option-code';
import {ColumnTitle} from 'akeneoassetmanager/application/component/app/column';

type OptionFilterViewProps = FilterViewProps & {
  context: {
    locale: string;
  };
};

const DEFAULT_OPERATOR = 'IN';

//TODO Use DSM Multiselect?
const OptionFilterView: FilterView = memo(({attribute, filter, onFilterUpdated, context}: OptionFilterViewProps) => {
  const translate = useTranslate();
  if (!(isOptionAttribute(attribute) || isOptionCollectionAttribute(attribute))) {
    return null;
  }

  const [isOpen, setIsOpen] = useState(false);

  const availableOptions = attribute.options.reduce(
    (availableOptions: {[choiceValue: string]: string}, option: Option) => {
      const normalizedOption: Option = option;
      availableOptions[normalizedOption.code] = getOptionLabel(option, context.locale);

      return availableOptions;
    },
    {} as {[label: string]: string}
  );

  const emptyFilter = () => {
    setIsOpen(false);
    onFilterUpdated({
      field: getAttributeFilterKey(attribute),
      operator: DEFAULT_OPERATOR,
      value: [],
      context: {},
    });
  };
  const value = undefined !== filter ? filter.value : [];
  const labels = value.map((optionCode: OptionCode) =>
    undefined !== availableOptions[optionCode] ? availableOptions[optionCode] : `[${optionCode}]`
  );
  const [position, setPosition] = useState({top: 0, left: 0});
  const labelRef = useRef<HTMLDivElement>(null);
  const openPanel = () => {
    setIsOpen(true);
    if (null !== labelRef.current) {
      const viewportOffset = labelRef.current.getBoundingClientRect();
      setPosition({top: viewportOffset.top, left: viewportOffset.left});
    }
  };

  return (
    <>
      <ColumnTitle ref={labelRef} onClick={openPanel}>
        {getLabel(attribute.labels, context.locale, attribute.code)}
      </ColumnTitle>
      <span className="AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited" onClick={openPanel}>
        <span className="AknFilterBox-filterCriteriaHint">
          {0 === labels.length ? translate('pim_asset_manager.asset.grid.filter.option.all') : labels.join(', ')}
        </span>
        <span className="AknFilterBox-filterCaret" />
      </span>
      {isOpen ? (
        <div>
          <div className="AknDropdown-mask" onClick={() => setIsOpen(false)} />
          <div
            className="AknFilterBox-filterDetails AknFilterBox-filterDetails--rightAlign"
            style={{top: `${position.top + 20}px`, left: `${position.left}px`, position: 'fixed'}}
          >
            <div className="AknFilterChoice">
              <div className="AknFilterChoice-header">
                <div className="AknFilterChoice-title">
                  {getLabel(attribute.labels, context.locale, attribute.code)}
                </div>
                <div className="AknIconButton AknIconButton--erase" onClick={emptyFilter} />
              </div>
              <div>
                <Select2
                  className="asset-option-selector"
                  data={availableOptions}
                  value={value}
                  multiple={true}
                  readOnly={false}
                  configuration={{
                    allowClear: true,
                    placeholder: translate('pim_asset_manager.asset.grid.filter.option.no_value'),
                    formatSelection: (data: any, _container: any, escapeMarkup: any) => {
                      const result = data ? escapeMarkup(data.text) : undefined;
                      if (result !== undefined) {
                        return '<div title="' + result + '">' + result + '</div>';
                      }

                      return result;
                    },
                    formatResult: (result: any, container: any, _query: any, _escapeMarkup: any) => {
                      const formerResult = '<span class="select2-match"></span>' + result.text;
                      container.attr('title', result.text);
                      return formerResult;
                    },
                  }}
                  onChange={(optionCodes: string[]) => {
                    onFilterUpdated({
                      field: getAttributeFilterKey(attribute),
                      operator: DEFAULT_OPERATOR,
                      value: optionCodes,
                      context: {},
                    });
                  }}
                />
              </div>
            </div>
          </div>
        </div>
      ) : null}
    </>
  );
});

export const filter = OptionFilterView;
