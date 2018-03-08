import * as React from 'react';
import Filter from 'pimfront/product-grid/domain/model/filter/filter';

interface FilterViewProps {
  filter: Filter;
}

interface FilterViewState {
  isOpen: boolean;
  filter: Filter;
}

export default class Boolean extends React.Component<FilterViewProps, FilterViewState> {
  constructor(props: FilterViewProps) {
    super(props);

    this.state = {
      isOpen: false,
      filter: props.filter,
    };
  }

  componentWillReceiveProps(nextProps: FilterViewState) {
    this.setState({filter: nextProps.filter});
  }

  render() {
    return (
      <div className="AknFilterBox-filterContainer" data-name="enabled" data-type="choice">
        <div className="AknFilterBox-filter filter-select filter-criteria-selector">
          <span className="AknFilterBox-filterLabel">Status</span>
          <select>
            <option value="">All</option>
            <option value="1">Enabled</option>
            <option value="0">Disabled</option>
          </select>
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
