import * as React from 'react';
import Dropdown, {DropdownElement} from 'pimfront/app/application/component/dropdown';
import BooleanPropertyFilter, {Choice} from 'pimfront/product-grid/domain/model/filter/property/boolean';
import BooleanAttributeFilter from 'pimfront/product-grid/domain/model/filter/attribute/boolean';
import {InvalidFilterModel} from 'pimfront/product-grid/application/component/filter/error';
import Filter from 'pimfront/product-grid/domain/model/filter/filter';
import __ from 'pimfront/tools/translator';

interface FilterViewProps {
  filter: BooleanAttributeFilter | BooleanPropertyFilter;
  locale: string;
  onFilterChanged: (filter: Filter) => void;
}

interface FilterViewState {}

const BooleanButtonView = ({
  label,
  selectedElement,
  onClick,
}: {
  label: string;
  selectedElement: DropdownElement;
  onClick: () => void;
}) => (
  <span onClick={onClick}>
    <span className="AknFilterBox-filterLabel">{label}</span>
    <span className="AknFilterBox-filterCriteria">
      {__(`pim_enrich.grid.product.filter.boolean.value.${selectedElement.identifier}`)}
    </span>
  </span>
);

export default class Boolean extends React.Component<FilterViewProps, FilterViewState> {
  constructor(props: FilterViewProps) {
    super(props);

    if (!(props.filter instanceof BooleanAttributeFilter || props.filter instanceof BooleanPropertyFilter)) {
      throw new InvalidFilterModel('The provided model is not compatible with the Boolean component');
    }
  }

  render() {
    const label =
      this.props.filter instanceof BooleanPropertyFilter
        ? __(this.props.filter.field.getLabel(this.props.locale))
        : this.props.filter.field.getLabel(this.props.locale);

    const selectedItem = this.props.filter.getChoiceFromFilter(this.props.filter);

    return (
      <div className="AknFilterBox-filterContainer" data-name={this.props.filter.field.identifier} data-type="choice">
        <div className="AknFilterBox-filter">
          <Dropdown
            elements={this.props.filter.getChoices().map((choice: Choice): DropdownElement => ({
              identifier: choice.identifier,
              label: __(`pim_enrich.grid.product.filter.boolean.value.${choice.identifier}`),
              original: choice,
            }))}
            ButtonView={BooleanButtonView}
            label={label}
            selectedElement={selectedItem.identifier}
            onSelectionChange={(choice: DropdownElement) => {
              const filter = this.props.filter.getFilterFromChoice(choice.original);

              this.props.onFilterChanged(filter);
            }}
          />
        </div>
        <span className="AknFilterBox-disableFilter AknIconButton AknIconButton--small AknIconButton--remove disable-filter" />
      </div>
    );
  }
}
