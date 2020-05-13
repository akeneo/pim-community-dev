import $ from 'jquery';
import React, { useRef, useEffect } from 'react';
import { Label } from '../Labels';

type option = {
  id: number | string;
  text: string;
  disabled?: boolean;
};

type optionsGroup = {
  id: number | string | null;
  text: string;
  children: option[];
  disabled?: boolean;
};

type Select2Event = {
  val: any;
} & JQuery.Event;

type ajaxResults = {
  more: boolean;
  results: option[] | optionsGroup[];
};

type InitSelectionCallback = (arg: option[] | option) => void;

type Props = {
  allowClear?: boolean;
  containerCssClass?: string;
  data?: (option | optionsGroup)[];
  dropdownCssClass?: string;
  formatResult?: (item: option | optionsGroup) => string;
  hiddenLabel?: boolean;
  id: string;
  label: string;
  onChange?: (value: string | string[]) => void;
  onSelecting?: (event: any) => void;
  placeholder?: string;
  value?: number | string | string[];
  multiple?: boolean;
  ajax?: {
    url: string;
    quietMillis?: number;
    cache?: boolean;
    data: (
      term: string,
      page: number
    ) => {
      search: string;
      options?: any;
    };
    results: (values: any) => ajaxResults;
  };
  initSelection?: (element: any, callback: InitSelectionCallback) => void;
};

const Select2Wrapper: React.FC<Props> = ({
  allowClear,
  containerCssClass,
  data,
  dropdownCssClass,
  formatResult,
  hiddenLabel = false,
  id,
  label,
  onChange,
  onSelecting,
  placeholder,
  value,
  multiple = false,
  ajax,
  initSelection,
}) => {
  const select2Ref = useRef<HTMLInputElement>(null);
  useEffect(() => {
    if (null === select2Ref.current) {
      return;
    }
    const $select = $(select2Ref.current) as any;
    $select.val(value);

    $select.select2('destroy');
    $select.select2({
      allowClear,
      containerCssClass,
      data,
      dropdownCssClass,
      formatResult,
      placeholder,
      multiple,
      ajax,
      initSelection,
    });

    if (onChange) {
      $select.on('change', (event: Select2Event) => onChange(event.val));
    }
    if (onSelecting) {
      $select.off('select2-selecting');
      $select.on('select2-selecting', onSelecting);
    }
  }, [select2Ref, onSelecting, onChange, formatResult]);

  useEffect(() => {
    if (null === select2Ref.current) {
      return;
    }
    const $select = $(select2Ref.current) as any;

    return () => {
      $select.off('change');
      $select.select2('destroy');
      $select.select2('container').remove();
    };
  }, [select2Ref]);

  return (
    <>
      <Label label={label} hiddenLabel={hiddenLabel} htmlFor={id} />
      <input id={id} type='hidden' ref={select2Ref} />
    </>
  );
};

export {
  Select2Wrapper,
  Select2Event,
  Props as Select2Props,
  option,
  optionsGroup,
  InitSelectionCallback,
  ajaxResults,
};
