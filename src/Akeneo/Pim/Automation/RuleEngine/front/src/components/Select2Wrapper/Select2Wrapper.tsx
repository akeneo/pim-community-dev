import $ from 'jquery';
import React, { useRef, useEffect } from 'react';
import { Label } from '../Labels';

type Select2Option = {
  id: number | string;
  text: string;
  disabled?: boolean;
};

type Select2OptionGroup = {
  id: number | string | null;
  text: string;
  children: Select2Option[];
  disabled?: boolean;
};

type Select2Event = {
  val: any;
} & JQuery.Event;

type InitSelectionCallback = (arg: Select2Option[] | Select2Option) => void;

type Select2Ajax = {
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
  results: (values: any) => {
    more: boolean;
    results: Select2Option[] | Select2OptionGroup[];
  };
  initSelection?: (element: any, callback: InitSelectionCallback) => void;
  hideSearch?: boolean;
};

type Select2Value = string | number;

type Select2GlobalProps = {
  allowClear?: boolean;
  containerCssClass?: string;
  dropdownCssClass?: string;
  hiddenLabel?: boolean;
  id: string;
  label: string;
  onSelecting?: (event: any) => void;
  placeholder?: string;
  initSelection?: (element: any, callback: InitSelectionCallback) => void;
  formatResult?: (item: Select2Option | Select2OptionGroup) => string;
  formatSelection?: (item: Select2Option | Select2OptionGroup) => string;
}

type Props = Select2GlobalProps & {
  data?: (Select2Option | Select2OptionGroup)[];
  onChange?: (value: Select2Value | Select2Value[]) => void;
  value?: Select2Value | Select2Value[];
  multiple: boolean;
  ajax?: Select2Ajax;
};

const Select2Wrapper: React.FC<Props> = ({
  allowClear,
  containerCssClass,
  data,
  dropdownCssClass,
  formatResult,
  formatSelection,
  hiddenLabel = false,
  id,
  label,
  onChange,
  onSelecting,
  placeholder,
  value,
  multiple,
  ajax,
  initSelection,
  hideSearch = false,
}) => {
  const select2Ref = useRef<HTMLInputElement>(null);
  const encodedData: string = JSON.stringify(data);

  const initSelect2 = () => {
    if (null === select2Ref.current) {
      return;
    }
    const $select = $(select2Ref.current) as any;
    $select.val(value);

    const options: any = {
      allowClear,
      containerCssClass,
      data,
      dropdownCssClass,
      formatResult,
      formatSelection,
      placeholder,
      multiple,
      ajax,
      initSelection,
    };
    if (hideSearch) {
      options.minimumResultsForSearch = Infinity;
    }
    $select.select2(options);

    if (onChange) {
      $select.on('change', (event: Select2Event) => onChange(event.val));
    }
  }

  useEffect(() => {
    if (null === select2Ref.current) {
      return;
    }
    const $select = $(select2Ref.current) as any;

    if (onChange) {
      $select.on('change', (event: Select2Event) => onChange(event.val));
    }
  }, [onChange]);

  useEffect(() => {
    if (null === select2Ref.current) {
      return;
    }
    const $select = $(select2Ref.current) as any;

    if (onSelecting) {
      $select.off('select2-selecting');
      $select.on('select2-selecting', onSelecting);
    }
  }, [onSelecting]);

  useEffect(() => {
    initSelect2();
  }, [encodedData]);

  useEffect(() => {
    if (null === select2Ref.current) {
      return;
    }
    const $select = $(select2Ref.current) as any;
    $select.val(value).trigger('change');
    if (onChange && value) {
      onChange(value);
    }
  }, [value]);

  useEffect(() => {
    if (null === select2Ref.current) {
      return;
    }
    const $select = $(select2Ref.current) as any;

    $select.select2('destroy');
    initSelect2($select);
  }, [formatResult])

  useEffect(() => {
    if (null === select2Ref.current) {
      return;
    }
    const $select = $(select2Ref.current) as any;

    $select.val(value);

    initSelect2($select);

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
  Select2Option,
  Select2OptionGroup,
  InitSelectionCallback,
  Select2GlobalProps,
  Select2Ajax,
  Select2Value,
};
