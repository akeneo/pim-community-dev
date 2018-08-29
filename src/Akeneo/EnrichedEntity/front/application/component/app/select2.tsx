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
  private events: {[eventName: string]: string} = {
    change: 'onChange',
  };

  componentDidMount() {
    this.el = $(ReactDOM.findDOMNode(this) as Element);

    if (undefined !== this.el.val(this.props.value).select2) {
      this.el.val(this.props.value).select2({allowClear: true});
      this.attachEventHandlers();
    }
  }

  componentWillUnmount() {
    Object.keys(this.events).forEach((eventName: string) => {
      this.el.off(eventName);
    });
  }

  private attachEventHandlers = () => {
    Object.keys(this.events).forEach((eventName: string) => {
      this.el.on(eventName, (e: any) => {
        this.props[this.events[eventName] as string](e.val);
      });
    });
  };

  render(): JSX.Element | JSX.Element[] {
    const {data, value, ...props} = this.props;

    return (
      <select
        id={props.fieldId}
        className="select2"
        name={props.fieldName}
        multiple={props.multiple}
        disabled={props.readonly}
        onChange={event => {
          const newValues = Array.prototype.slice
            .call(event.currentTarget.childNodes)
            .filter((option: HTMLOptionElement) => option.selected)
            .map((option: HTMLOptionElement) => option.value);

          this.props.onChange(newValues);
        }}
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
