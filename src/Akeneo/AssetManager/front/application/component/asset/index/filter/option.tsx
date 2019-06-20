import * as React from 'react';
import {FilterView, FilterViewProps} from 'akeneoassetmanager/application/configuration/value';
import {ConcreteOptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {
  Option,
  NormalizedOption,
  NormalizedOptionCode,
} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {connect} from 'react-redux';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
import __ from 'akeneoassetmanager/tools/translator';
import {ConcreteOptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import {getAttributeFilterKey} from 'akeneoassetmanager/tools/filter';

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

  const availableOptions = attribute.getOptions().reduce(
    (availableOptions: {[choiceValue: string]: string}, option: Option) => {
      const normalizedOption: NormalizedOption = option.normalize();
      availableOptions[normalizedOption.code] = option.getLabel(context.locale);

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
  const labels = value.map((optionCode: NormalizedOptionCode) =>
    undefined !== availableOptions[optionCode] ? availableOptions[optionCode] : `[${optionCode}]`
  );

  return (
    <React.Fragment>
      <span className="AknFilterBox-filterLabel" onClick={() => setIsOpen(true)}>
        {attribute.getLabel(context.locale)}
      </span>
      <span
        className="AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited"
        onClick={() => setIsOpen(true)}
      >
        <span className="AknFilterBox-filterCriteriaHint">
          {0 === labels.length ? __('pim_asset_manager.asset.grid.filter.option.all') : labels.join(', ')}
        </span>
        <span className="AknFilterBox-filterCaret" />
      </span>
      {isOpen ? (
        <div>
          <div className="AknDropdown-mask" onClick={() => setIsOpen(false)} />
          <div className="AknFilterBox-filterDetails">
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

export const filter = connect(
  (state: EditState, ownProps: FilterViewProps): OptionFilterViewProps => {
    return {
      ...ownProps,
      context: {
        locale: state.user.catalogLocale,
      },
    };
  }
)(OptionFilterView);
