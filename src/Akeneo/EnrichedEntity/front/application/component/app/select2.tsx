import * as React from 'react';
import * as ReactDOM from 'react-dom';
import * as $ from 'jquery';

export interface Select2Props {
  fieldId: string;
  fieldName: string;
  data: {
    [choiceValue: string]: string;
  };
  value: string | string[];
  multiple: boolean;
  readonly: boolean;
  onSelect: (value: string) => void;
  onUnselect: (value: string) => void;
}

export default class Select2 extends React.Component<Select2Props> {
  public props: any;
  private el: any;
  private events: any = [
    ['select2-selecting', 'onSelect'],
    ['change', 'onUnselect']
  ];

  componentDidMount() {
    this.el = $(ReactDOM.findDOMNode(this) as Element);
    this.el.select2({allowClear: true});
    this.attachEventHandlers();
  }

  componentWillUnmount() {
    this.events.map((event: any) => {
      this.el.off(event[0]);
    });
  }

  private attachEventHandlers = () => {
    this.events.map((event: any) => {
      if (typeof this.props[event[1]] !== 'undefined') {
        this.el.on(event[0], (e: any) => {
          // Need to do this crapy trick as we don't have a removed event triggered on our select2 library version
          const value = ('change' === event[0] && e.removed) ? e.removed.id : e.val;
          this.props[event[1] as string](value);
        });
      }
    });
  }

  render(): JSX.Element | JSX.Element[] {
    const {data, value, ...props} = this.props;

    return (
      <select
        id={props.fieldId}
        className="select2"
        name={props.fieldName}
        multiple={props.multiple}
        disabled={props.readonly}
        defaultValue={value}
      >
        {Object.keys(data).map((choiceValue: string) => {
          return (
            <option key={choiceValue} value={choiceValue}>
              {data[choiceValue]}
            </option>
          );
        })}
      </select>
    );
  }
}
