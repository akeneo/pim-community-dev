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
  const field = 'sku';
  const value = 'all';

  return (
    <div>
      <div className="AknFilterBox-filterContainer filter-item" data-name="{field}" data-type="string">
        <div className="AknFilterBox-filter filter-criteria-selector oro-drop-opener">
          <span className="AknFilterBox-filterLabel">{field}</span>
          <span className="AknFilterBox-filterCriteria filter-criteria-hint">{value}</span>
          <span className="AknFilterBox-filterCaret" />
        </div>
        <div className="filter-criteria">
          <div className="AknFilterChoice choicefilter">
            <div className="AknFilterChoice-header">
              <div className="AknFilterChoice-title">{field}</div>
              <Dropdown
                elements={operators}
                label={trans.get('pim.grid.choice_filter.operator')}
                selectedElement="CONTAINS"
                onSelectionChange={() => {}}
              />
            </div>
          </div>
        </div>
        <div>
          <input type="text" name="value" className="AknTextField select-field" />
        </div>
        <div className="AknFilterChoice-button">
          <button type="button" className="AknButton AknButton--apply filter-update">{trans.get('Update')}</button>
        </div>
      </div>
      <div className="AknFilterBox-disableFilter AknIconButton AknIconButton--small AknIconButton--remove disable-filter"></div>
    </div>
  );
}
