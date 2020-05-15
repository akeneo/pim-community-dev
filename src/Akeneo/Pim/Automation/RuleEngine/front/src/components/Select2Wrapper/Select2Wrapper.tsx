import React, { useEffect, useRef } from 'react';
import { Label } from '../Labels';
import { useFormContext } from 'react-hook-form';

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
  results: (
    values: any
  ) => {
    more: boolean;
    results: Select2Option[] | Select2OptionGroup[];
  };
  initSelection?: (element: any, callback: InitSelectionCallback) => void;
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
  initSelection?: (
    element: { val: any },
    callback: InitSelectionCallback
  ) => void;
  formatResult?: (item: Select2Option | Select2OptionGroup) => string;
  formatSelection?: (item: Select2Option | Select2OptionGroup) => string;
  hideSearch?: boolean;
  name?: string;
};

type Props = Select2GlobalProps & {
  data?: (Select2Option | Select2OptionGroup)[];
  onChange?: (value: Select2Value | Select2Value[]) => void;
  value?: Select2Value | Select2Value[];
  multiple: boolean;
  ajax?: Select2Ajax;
};

const Select2Wrapper: React.FC<Props> = ({
  hiddenLabel = false,
  id,
  label,
  name,
  ajax,
  data,
  multiple,
  initSelection,
  placeholder,
  formatSelection,
  formatResult,
  containerCssClass,
  dropdownCssClass,
  onSelecting,
}) => {
  const select2ref = useRef<HTMLInputElement | null>(null);
  const { watch, setValue } = useFormContext();

  const getFormValue: any = () => {
    return name ? watch(name) : undefined;
  };

  const setFormValue = (value: any) => {
    if (name) {
      setValue(name, value);
    }
  };

  const getSelect2Input: () => any = () => {
    return $(select2ref.current as any) as any;
  };

  const initSelect2 = (destroy = false) => {
    if (select2ref !== null) {
      if (destroy) {
        getSelect2Input().select2('destroy');
      }
      if (undefined !== getFormValue()) {
        getSelect2Input().val(getFormValue());
      }
      getSelect2Input().select2({
        ajax,
        data,
        multiple,
        initSelection,
        placeholder,
        formatSelection,
        formatResult,
        containerCssClass,
        dropdownCssClass,
      });

      if (onSelecting) {
        getSelect2Input().off('select2-selecting');
        getSelect2Input().on('select2-selecting', onSelecting);
      }

      getSelect2Input().on('change', (e: Select2Event) => {
        setFormValue(e.val);
      });
    }
  };

  useEffect(() => {
    initSelect2();
  }, [select2ref]);

  useEffect(() => {
    if (name) {
      const value = getFormValue();
      if (select2ref !== null && value !== undefined) {
        getSelect2Input()
          .val(value)
          .trigger('change.select2');
      }
    }
  }, [getFormValue()]);

  useEffect(() => {
    initSelect2(true);
  }, [JSON.stringify(data)]);

  useEffect(() => {
    initSelect2(true);
  }, [onSelecting]);

  return (
    <>
      <Label label={label} hiddenLabel={hiddenLabel} htmlFor={id} />
      <input id={id} type='hidden' ref={select2ref} name={name} />
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
