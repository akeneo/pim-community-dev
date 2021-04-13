import React from 'react';
import $ from 'jquery';
import styled from 'styled-components';
import {AkeneoThemedProps} from 'akeneo-design-system';

const StyledSelect = styled.select<{light: boolean}>`
  ${({light}: AkeneoThemedProps<{light: boolean}>) =>
    light &&
    `
    .select2-choice {
      border: none;
      padding: 0;
      width: fit-content;
      background: inherit;
      line-height: inherit;
      height: initial;

      .select2-arrow {
        background-position: bottom left;
      }
    }
`}
`;

export type Select2Options = {
  [value: string]: string;
};

export interface Select2Props {
  data: Select2Options;
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
      $el.val(this.props.value).select2(this.props.configuration || {});
      $el.on('change', (event: any) => {
        this.props.onChange(event.val);
      });
    }
  }

  componentDidUpdate() {
    if (null === this.select.current) {
      return;
    }

    const {value} = this.props;
    const $el = $(this.select.current) as any;

    if (value.length === 0) {
      return;
    }

    if (undefined !== $el.select2) {
      $('#select2-drop-mask, #select2-drop').remove();
      $el.val(value).select2(this.props.configuration || {});
    }
  }

  componentWillUnmount() {
    if (null === this.select.current) {
      return;
    }
    const $el = $(this.select.current) as any;

    $el.off('change');
    $el.select2('destroy');
  }

  render(): JSX.Element | JSX.Element[] {
    const {data, value, configuration, ...props} = this.props;

    return (
      <StyledSelect
        {...props}
        ref={this.select}
        value={value}
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
      </StyledSelect>
    );
  }
}
