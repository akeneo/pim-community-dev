import __ from 'akeneoreferenceentity/tools/translator';
import * as React from 'react';

type Props = {
  value: string;
  delay: number;
  onChange: (newValue: string) => void;
};

export default class SearchField extends React.Component<Props> {
  private timer = null;
  static defaultProps = {
    delay: 250,
  };

  /**
   * This method is triggered each time the user types on the search field
   * It dispatches events only if the user pauses for more than 100ms
   */
  onSearchUpdated(event: React.ChangeEvent<HTMLInputElement>) {
    const userSearch = event.currentTarget.value;
    if (null !== this.timer) {
      clearTimeout(this.timer);
    }
    this.timer = setTimeout(() => {
      this.props.onChange(userSearch);
    }, this.props.delay) as any;
  }

  render() {
    const {value, onChange} = this.props;

    return (
      <div className="AknFilterBox-searchContainer">
        <input
          type="text"
          className="AknFilterBox-search"
          placeholder={__('pim_reference_entity.record.grid.search')}
          defaultValue={this.props.value}
          onChange={this.onSearchUpdated.bind(this)}
        />
      </div>
    );
  }
}
