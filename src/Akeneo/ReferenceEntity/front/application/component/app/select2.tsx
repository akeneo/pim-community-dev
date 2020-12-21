import * as React from 'react';
import $ from 'jquery';

export interface Select2Props {
  data: {
    [choiceValue: string]: string;
  };
  value: string[] | string;
  multiple: boolean;
  readOnly: boolean;
  configuration?: any;
  onChange: (value: string[] | string) => void;
}

export default class Select2 extends React.Component<Select2Props & any> {
  public props: Select2Props & any;
  private select: React.RefObject<HTMLSelectElement>;

  constructor(props: Select2Props & any) {
    super(props);

    this.select = React.createRef<HTMLSelectElement>();
  }

  componentDidMount() {
    if (null === this.select.current) {
      return;
    }
    const $el = $(this.select.current) as any;

    if (undefined !== $el.select2) {
      $el.val(this.props.value).select2(this.props.configuration);
      $el.on('change', (event: any) => {
        this.props.onChange(event.val);
      });
    }
  }

  componentDidUpdate(prevProps: Select2Props & any) {
    if (null === this.select.current) {
      return;
    }

    const {value} = this.props;
    const $el = $(this.select.current) as any;
    if (!$el.select2) {
      return;
    }

    if (prevProps.value !== value && value.length === 0) {
      // Workaround for PIM-9560 : setting $el.val() with empty value in the record option filter
      // causes crash during the call of rest/reference_entity/ref_entity/record
      // The following condition excludes this filter.
      if ('record-option-selector' !== this.props.className) {
        // The re-render can bring some problem when the value becomes empty (see PIM-8479)
        // but we need to set the value in any case (see PIM-9263).
        // PIM-9560 : set to null for single select, [] for multi-select
        let emptyVal = 'object' === typeof value ? [] : null;
        $el.val(emptyVal).trigger('change');
      }
      return;
    }

    $('#select2-drop-mask, #select2-drop').remove();
    $el.val(value).select2(this.props.configuration);
  }

  componentWillUnmount() {
    if (null === this.select.current) {
      return;
    }
    const $el = $(this.select.current) as any;

    $el.off('change');
  }

  render(): JSX.Element | JSX.Element[] {
    const {data, value, configuration, ...props} = this.props;

    return (
      <select
        {...props}
        ref={this.select}
        multiple={props.multiple}
        disabled={props.readOnly}
        onChange={event => {
          const newValues = Array.prototype.slice // used to convert node list into an array
            .call(event.currentTarget.childNodes)
            .filter((option: HTMLOptionElement) => option.selected)
            .map((option: HTMLOptionElement) => option.value);

          this.props.onChange(this.props.multiple ? newValues : newValues[0]);
        }}
      >
        {/*In case of simple select and allow clear, we need to add an empty option for Select2*/}
        {undefined !== configuration && true === configuration.allowClear && false === props.multiple ? (
          <option />
        ) : null}
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
