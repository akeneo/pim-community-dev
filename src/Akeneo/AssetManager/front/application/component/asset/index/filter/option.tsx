import * as React from 'react';
import {FilterView, FilterViewProps} from 'akeneoassetmanager/application/configuration/value';
import {ConcreteOptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {Option, getOptionLabel} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
import __ from 'akeneoassetmanager/tools/translator';
import {ConcreteOptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import {getAttributeFilterKey} from 'akeneoassetmanager/tools/filter';
import OptionCode from 'akeneoassetmanager/domain/model/attribute/type/option/option-code';

const memo = (React as any).memo;
const useState = (React as any).useState;

type OptionFilterViewProps = FilterViewProps & {
  context: {
    locale: string;
  };
};

const DEFAULT_OPERATOR = 'IN';

const OptionFilterView: FilterView = memo(({attribute, filter, onFilterUpdated, context}: OptionFilterViewProps) => {
  if (!(attribute instanceof ConcreteOptionAttribute || attribute instanceof ConcreteOptionCollectionAttribute)) {
    return null;
  }

  const [isOpen, setIsOpen] = useState(false);

  const availableOptions = attribute
    .getOptions()
    .reduce((availableOptions: {[choiceValue: string]: string}, option: Option) => {
      const normalizedOption: Option = option;
      availableOptions[normalizedOption.code] = getOptionLabel(option, context.locale);

      return availableOptions;
    }, {} as {[label: string]: string});

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
  const [position, setPosition] = React.useState({top: 0, left: 0});
  const labelRef = React.useRef<HTMLSpanElement>(null);
  const openPanel = () => {
    setIsOpen(true);
    if (null !== labelRef.current) {
      const viewportOffset = labelRef.current.getBoundingClientRect();
      setPosition({top: viewportOffset.top, left: viewportOffset.left});
    }
  };

  return (
    <React.Fragment>
      <span ref={labelRef} className="AknFilterBox-filterLabel" onClick={() => openPanel}>
        {attribute.getLabel(context.locale)}
      </span>
      <span className="AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited" onClick={openPanel}>
        <span className="AknFilterBox-filterCriteriaHint">
          {0 === labels.length ? __('pim_asset_manager.asset.grid.filter.option.all') : labels.join(', ')}
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
                <div className="AknFilterChoice-title">{attribute.getLabel(context.locale)}</div>
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
                    placeholder: __('pim_asset_manager.asset.grid.filter.option.no_value'),
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
    </React.Fragment>
  );
});

export const filter = OptionFilterView;
