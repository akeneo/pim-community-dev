import $ from 'jquery';
import React, { useRef, useEffect } from 'react';
import { Label } from '../Labels';

type option = { id: number | string; text: string };

type Select2Event = {
  val: any;
} & JQuery.Event;

type ajaxResults = {
  more: boolean;
  results: option[];
};

type InitSelectionCallback = (arg: option[]) => void;

type Props = {
  allowClear?: boolean;
  containerCssClass?: string;
  data?: option[];
  dropdownCssClass?: string;
  formatResult?: (item: { id: string }) => string;
  hiddenLabel?: boolean;
  id: string;
  label: string;
  onChange: (value: string | string[]) => void;
  placeholder?: string;
  value: string | string[];
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
    $select.on('change', (event: Select2Event) => onChange(event.val));
    return () => {
      $select.off('change');
      $select.select2('destroy');
      $select.select2('container').remove();
    };
  }, [select2Ref]);

  useEffect(() => {
    if (null === select2Ref.current) {
      return;
    }
    const $select = $(select2Ref.current) as any;
    $select.on('change', (event: Select2Event) => onChange(event.val));
  }, [onChange]);

  return (
    <>
      <Label label={label} hiddenLabel={hiddenLabel} htmlFor={id} />
      <input id={id} type='hidden' ref={select2Ref}/>
    </>
  );
};

export {
  Select2Wrapper,
  Select2Event,
  Props as Select2Props,
  option,
  InitSelectionCallback,
  ajaxResults,
};
