import * as React from 'react';
import Dropdown, {DropdownElement} from 'pimfront/app/application/component/dropdown';
import BooleanPropertyFilter, {Choice} from 'pimfront/product-grid/domain/model/filter/property/boolean';
import BooleanAttributeFilter from 'pimfront/product-grid/domain/model/filter/attribute/boolean';
import {InvalidFilterModel} from 'pimfront/product-grid/application/component/filter/error';

interface FilterViewProps {
  filter: BooleanAttributeFilter | BooleanPropertyFilter;
  onFilterChange: (filter: Filter) => void;
}

interface FilterViewState {
  isOpen: boolean;
  selectedItem: Choice;
}

export default class Boolean extends React.Component<FilterViewProps, FilterViewState> {
  constructor(props: FilterViewProps) {
    super(props);

    if (!(props.filter instanceof BooleanAttributeFilter || props.filter instanceof BooleanPropertyFilter)) {
      throw new InvalidFilterModel('The provided model is not compatible witht the Boolean component');
    }

    this.state = {
      isOpen: false,
      selectedItem: props.filter.getChoiceFromFilter(props.filter),
    };
  }

  render() {
    return (
      <div className="AknFilterBox-filterContainer" data-name="enabled" data-type="choice">
        <div className="AknFilterBox-filter filter-select filter-criteria-selector">
          <span className="AknFilterBox-filterLabel">Status</span>
          <Dropdown
            elements={this.props.filter.getChoices().map((choice: Choice): DropdownElement => ({
              identifier: choice.identifier,
              label: `${choice.identifier}`,
              original: choice,
            }))}
            label={'boolean'}
            selectedElement={this.state.selectedItem.identifier}
            onSelectionChange={(choice: DropdownElement) => {
              const filter = this.props.filter.getFilterFromChoice(choice.original);

              this.props.onFilterChange(filter);
              // const locale = locales.find((locale: Locale) => locale.code === selection);

              // if (undefined !== locale) {
              //   onLocaleChange(locale);
              // }
            }}
          />
          <button
            type="button"
            className="ui-multiselect ui-corner-all AknFilterBox-filterCriteria select-filter-widget"
            aria-haspopup="true"
          >
            <span className="filter-criteria-hint">All</span>
            <span className="AknFilterBox-filterCaret" />
          </button>
        </div>
        <a
          href="javascript:void(0);"
          className="AknFilterBox-disableFilter AknIconButton AknIconButton--small AknIconButton--remove disable-filter"
        />
      </div>
    );
  }
}
