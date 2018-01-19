import * as React from 'react';
import Dropdown from 'pimfront/app/application/component/dropdown';
import * as trans from 'pimenrich/lib/translator';

const operators = [
  {
    identifier: 'CONTAINS',
    label: 'contains'
  },
  {
    identifier: 'DOES_NOT_CONTAIN',
    label: 'does not contain'
  },
  {
    identifier: 'DOES_NOT_CONTAIN',
    label: 'does not contain'
  },
  {
    identifier: 'EQUALS',
    label: 'is equal to'
  },
  {
    identifier: 'STARTS_WITH',
    label: 'starts with'
  },
  {
    identifier: 'IS_EMPTY',
    label: 'is empty'
  },
  {
    identifier: 'IN_LIST',
    label: 'in list'
  }
];

export default () => {
  return (
    <div>
      <div className="AknFilterBox-filterContainer filter-item oro-drop" data-name="sku" data-type="string">
        <div className="AknFilterBox-filter filter-criteria-selector oro-drop-opener">
          <span className="AknFilterBox-filterLabel">SKU</span>
          <span className="AknFilterBox-filterCriteria filter-criteria-hint">All</span>
          <span className="AknFilterBox-filterCaret" />
        </div>
        <div className="filter-criteria dropdown-menu">
          <div className="AknFilterChoice choicefilter">
            <div className="AknFilterChoice-header">
              <div className="AknFilterChoice-title">SKU</div>
              <Dropdown
                elements={operators}
                label={trans.get('pim.grid.choice_filter.operator')}
                selectedElement="CONTAINS"
                onSelectionChange={() => {}}
              />

              // <div className="AknDropdown operator">
              //   <div className="AknActionButton AknActionButton--withoutBorder" data-toggle="dropdown">
              //     <span className="AknActionButton-highlight">contains</span>
              //     <span className="AknActionButton-caret" />
              //   </div>
              //   <div className="AknDropdown-menu">
              //     <div className="AknDropdown-menuTitle">Operator</div>
              //     <div className="AknDropdown-menuLink AknDropdown-menuLink--active active">
              //       <span className="label operator_choice" data-value="1">contains</span>
              //     </div>
              //     <div className="AknDropdown-menuLink">
              //         <span className="label operator_choice" data-value="2">does not contain</span>
              //     </div>
              //     <div className="AknDropdown-menuLink">
              //         <span className="label operator_choice" data-value="3">is equal to</span>
              //     </div>
              //     <div className="AknDropdown-menuLink">
              //         <span className="label operator_choice" data-value="4">starts with</span>
              //     </div>
              //     <div className="AknDropdown-menuLink">
              //         <span className="label operator_choice" data-value="empty">is empty</span>
              //     </div>
              //     <div className="AknDropdown-menuLink">
              //         <span className="label operator_choice" data-value="in">in list</span>
              //     </div>
              //   </div>
              // </div>
            </div>
          </div>
        </div>
        <div>
          <input type="text" name="value" className="AknTextField select-field" />
        </div>
        <div className="AknFilterChoice-button">
          <button type="button" className="AknButton AknButton--apply filter-update">Update</button>
        </div>
      </div>
      <div className="AknFilterBox-disableFilter AknIconButton AknIconButton--small AknIconButton--remove disable-filter"></div>
    </div>
  );
}
