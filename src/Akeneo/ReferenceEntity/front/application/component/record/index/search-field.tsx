import __ from 'akeneoreferenceentity/tools/translator';
import * as React from 'react';

type Props = {
  value: string;
  changeThreshold: number;
  onChange: (newValue: string) => void;
};

export default class SearchField extends React.Component<Props> {
  private defaultFocus: React.RefObject<any>;
  private timer = null;
  static defaultProps = {
    changeThreshold: 250,
  };

  constructor(props: Props) {
    super(props);

    this.defaultFocus = React.createRef();
  }

  componentDidMount() {
    if (null !== this.defaultFocus.current) {
      this.defaultFocus.current.focus();
    }
  }

  /**
   * This method is triggered each time the user types on the search field
   * It dispatches events only if the user pauses for more than 100ms
   */
  onSearchUpdated(event: React.ChangeEvent<HTMLInputElement>) {
    const userSearch = event.currentTarget.value;
    if (null !== this.timer) {
      clearTimeout(this.timer as any);
    }
    this.timer = setTimeout(() => {
      this.props.onChange(userSearch);
    }, this.props.changeThreshold) as any;
  }

  render() {
    return (
      <div className="AknFilterBox-searchContainer">
        <input
          type="text"
          autoComplete="off"
          className="AknFilterBox-search"
          placeholder={__('pim_reference_entity.record.grid.search')}
          defaultValue={this.props.value}
          onChange={this.onSearchUpdated.bind(this)}
          ref={this.defaultFocus}
        />
      </div>
    );
  }
}
