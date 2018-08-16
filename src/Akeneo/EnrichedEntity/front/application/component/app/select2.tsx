import * as React from 'react';
import * as ReactDOM from 'react-dom';
import * as $ from 'jquery';

export interface Select2Props {
  fieldId: string;
  fieldName: string;
  data: {
    [choiceValue: string]: string;
  };
  value: string[];
  multiple: boolean;
  readonly: boolean;
  onSelect: (value: string) => void;
  onUnselect: (value: string) => void;
}

export default class Select2 extends React.Component<Select2Props> {
  public props: any;
  private el: any;
  private events: any = [
    ['change', 'onChange']
  ];

  componentDidMount() {
    this.el = $(ReactDOM.findDOMNode(this) as Element);
    this.el.val(this.props.value).select2({allowClear: true});
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
          this.props[event[1] as string](e.val);
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
