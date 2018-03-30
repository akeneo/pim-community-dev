import * as React from 'react';
import TextPropertyFilter from 'pimfront/product-grid/domain/model/filter/property/boolean';
import TextAttributeFilter from 'pimfront/product-grid/domain/model/filter/attribute/boolean';
import {InvalidFilterModel} from 'pimfront/product-grid/application/component/filter/error';
import Filter from 'pimfront/product-grid/domain/model/filter/filter';
import All from 'pimfront/product-grid/domain/model/filter/operator/all';
import __ from 'pimfront/tools/translator';

interface FilterViewProps {
  filter: TextAttributeFilter | TextPropertyFilter;
  locale: string;
  onFilterChanged: (filter: Filter) => void;
}

interface FilterViewState {
  isOpen: boolean;
}

const toString = (filter: Filter) => {
  return filter.operator.equals(All.create())
    ? __('pim_enrich.grid.product.filter.text.label.all')
    : __('pim_enrich.grid.product.filter.text.label.' + filter.operator.identifier, {value: filter.value.toString()});
};

export default class Text extends React.Component<FilterViewProps, FilterViewState> {
  constructor(props: FilterViewProps) {
    super(props);

    if (!(props.filter instanceof TextAttributeFilter || props.filter instanceof TextPropertyFilter)) {
      throw new InvalidFilterModel(`The provided model is not compatible witht the Text component
Given: ${JSON.stringify(props.filter)}`);
    }

    this.state = {
      isOpen: false,
    };
  }

  render() {
    const label =
      this.props.filter instanceof TextPropertyFilter
        ? __(this.props.filter.field.getLabel(this.props.locale))
        : this.props.filter.field.getLabel(this.props.locale);

    return (
      <div className="AknFilterBox-filterContainer" data-name={this.props.filter.field.identifier} data-type="string">
        <div className="AknFilterBox-filter">
          <span className="AknFilterBox-filterLabel">{label}</span>
          <span className="AknFilterBox-filterCriteria">{toString(this.props.filter)}</span>
        </div>
        <span className="AknFilterBox-disableFilter AknIconButton AknIconButton--small AknIconButton--remove disable-filter" />
      </div>
    );
  }
}

// <div class="AknFilterBox-filterContainer filter-item oro-drop open-filter" data-name="sku" data-type="string">
// <div class="AknFilterBox-filter filter-criteria-selector oro-drop-opener">
// <span class="AknFilterBox-filterLabel">SKU</span>
// <span class="AknFilterBox-filterCriteria filter-criteria-hint">All</span>
// <span class="AknFilterBox-filterCaret"></span></div><div class="filter-criteria dropdown-menu" style="display: block; position: fixed; left: 109px; top: 334px;"><div class="AknFilterChoice choicefilter">
//     <div class="AknFilterChoice-header">
//         <div class="AknFilterChoice-title">SKU</div>

//         <div class="AknDropdown operator">
//             <div class="AknActionButton AknActionButton--withoutBorder" data-toggle="dropdown">
//                 <span class="AknActionButton-highlight">contains</span>
//                 <span class="AknActionButton-caret"></span>
//             </div>
//             <div class="AknDropdown-menu">
//                 <div class="AknDropdown-menuTitle">Operator</div>

//                 <div class="AknDropdown-menuLink AknDropdown-menuLink--active active">
//                     <span class="label operator_choice" data-value="1">contains</span>
//                 </div>

//                 <div class="AknDropdown-menuLink">
//                     <span class="label operator_choice" data-value="2">does not contain</span>
//                 </div>

//                 <div class="AknDropdown-menuLink">
//                     <span class="label operator_choice" data-value="3">is equal to</span>
//                 </div>

//                 <div class="AknDropdown-menuLink">
//                     <span class="label operator_choice" data-value="4">starts with</span>
//                 </div>

//                 <div class="AknDropdown-menuLink">
//                     <span class="label operator_choice" data-value="empty">is empty</span>
//                 </div>

//                 <div class="AknDropdown-menuLink">
//                     <span class="label operator_choice" data-value="in">in list</span>
//                 </div>

//             </div>
//         </div>

//     </div>
//     <div>
//         <input type="text" name="value" class="AknTextField select-field" style="display: inline-block;">
//     </div>
//     <div class="AknFilterChoice-button">
//         <button type="button" class="AknButton AknButton--apply filter-update">Update</button>
//     </div>
// </div>
// </div><div class="AknFilterBox-disableFilter AknIconButton AknIconButton--small AknIconButton--remove disable-filter"></div></div>
