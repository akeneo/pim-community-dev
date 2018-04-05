import * as React from 'react';
import TextAttributeFilter from 'pimfront/product-grid/domain/model/filter/attribute/text';
// import TextAttributeFilter from 'pimfront/product-grid/domain/model/filter/attribute/boolean';
import {InvalidFilterModel} from 'pimfront/product-grid/application/component/filter/error';
import Filter from 'pimfront/product-grid/domain/model/filter/filter';
import All from 'pimfront/product-grid/domain/model/filter/operator/all';
import __ from 'pimfront/tools/translator';
import Dropdown, {DropdownElement} from 'pimfront/app/application/component/dropdown';
import {Operator} from 'pimfront/product-grid/domain/model/filter/operator';
import {Value, String} from 'pimfront/product-grid/domain/model/filter/value';

interface FilterViewProps {
  // filter: TextAttributeFilter | TextAttributeFilter;
  filter: TextAttributeFilter;
  locale: string;
  onFilterChanged: (filter: Filter) => void;
}

interface FilterViewState {
  isOpen: boolean;
  value: string | string[];
}

const toString = (filter: Filter) => {
  return filter.operator.equals(All.create())
    ? __('pim_enrich.grid.product.filter.text.label.all')
    : __('pim_enrich.grid.product.filter.text.label.' + filter.operator.identifier, {value: filter.value.toString()});
};

export default class Text extends React.Component<FilterViewProps, FilterViewState> {
  constructor(props: FilterViewProps) {
    super(props);

    // if (!(props.filter instanceof TextAttributeFilter || props.filter instanceof TextAttributeFilter)) {
    if (!(props.filter instanceof TextAttributeFilter)) {
      throw new InvalidFilterModel(`The provided model is not compatible with the Text component
Given: ${JSON.stringify(props.filter)}`);
    }

    this.state = {
      isOpen: false,
      value: props.filter.value.getValue(),
    };
  }

  static getDerivedStateFromProps(nextProps: FilterViewProps, prevState: FilterViewState) {
    return nextProps.filter.value.getValue() !== prevState.value ? {value: nextProps.filter.value.getValue()} : null;
  }

  private open() {
    this.setState({isOpen: true});
  }

  private close() {
    this.setState({isOpen: false});
  }

  private onOperatorChange(operator: Operator) {
    this.props.onFilterChanged(this.props.filter.setOperator(operator));
  }

  private onValueChange(value: Value) {
    this.props.onFilterChanged(this.props.filter.setValue(value));
  }

  private onStringValueChange(value: string) {
    this.onValueChange(String.fromValue(value));
  }

  private renderView(label: string) {
    const {filter} = this.props;

    return (
      <div className="AknDropdown-menu AknDropdown-menu--open">
        <div className="AknFilterChoice choicefilter">
          <div className="AknFilterChoice-header">
            <div className="AknFilterChoice-title">{label}</div>
            <Dropdown
              onSelectionChange={(element: DropdownElement) => this.onOperatorChange(element.original)}
              label={filter.operator.identifier}
              selectedElement={filter.operator.identifier}
              elements={filter.getOperators().map((operator: Operator): DropdownElement => ({
                identifier: operator.identifier,
                label: operator.identifier,
                original: operator,
              }))}
            />
            <input
              type="text"
              value={this.state.value}
              onChange={event => {
                this.onStringValueChange(event.target.value);
              }}
            />
          </div>
        </div>
      </div>
    );
  }

  render() {
    const label =
      this.props.filter instanceof TextAttributeFilter ? __(this.props.filter.field.getLabel(this.props.locale)) : ''; //this.props.filter.field.getLabel(this.props.locale);

    return (
      <div className="AknFilterBox-filterContainer" data-name={this.props.filter.field.identifier} data-type="string">
        {this.state.isOpen ? <div className="AknDropdown-mask" onClick={this.close.bind(this)} /> : null}
        <div className="AknFilterBox-filter" onClick={this.open.bind(this)}>
          <span className="AknFilterBox-filterLabel">{label}</span>
          <span className="AknFilterBox-filterCriteria">{toString(this.props.filter)}</span>
        </div>
        <span className="AknFilterBox-disableFilter AknIconButton AknIconButton--small AknIconButton--remove disable-filter" />
        {this.state.isOpen ? this.renderView(label) : null}
      </div>
    );
  }
}

// <div className="AknFilterChoice choicefilter">
//   <div className="AknFilterChoice-header">
//     <div className="AknFilterChoice-title">{label}</div>
//     <Dropdown

//     />

//     <div className="AknDropdown operator">
//       <div className="AknActionButton AknActionButton--withoutBorder" data-toggle="dropdown">
//         <span className="AknActionButton-highlight">contains</span>
//         <span className="AknActionButton-caret"></span>
//       </div>
//       <div className="AknDropdown-menu">
//         <div className="AknDropdown-menuTitle">Operator</div>

//         <div className="AknDropdown-menuLink AknDropdown-menuLink--active active">
//           <span className="label operator_choice" data-value="1">contains</span>
//         </div>

//         <div className="AknDropdown-menuLink">
//           <span className="label operator_choice" data-value="2">does not contain</span>
//         </div>

//         <div className="AknDropdown-menuLink">
//           <span className="label operator_choice" data-value="3">is equal to</span>
//         </div>

//         <div className="AknDropdown-menuLink">
//           <span className="label operator_choice" data-value="4">starts with</span>
//         </div>

//         <div className="AknDropdown-menuLink">
//           <span className="label operator_choice" data-value="empty">is empty</span>
//         </div>

//         <div className="AknDropdown-menuLink">
//           <span className="label operator_choice" data-value="in">in list</span>
//         </div>

//       </div>
//     </div>

//   </div>
//   <div>
//     <input type="text" name="value" className="AknTextField select-field">
//     </div>
//     <div className="AknFilterChoice-button">
//       <button type="button" className="AknButton AknButton--apply filter-update">Update</button>
//     </div>
//   </div>
