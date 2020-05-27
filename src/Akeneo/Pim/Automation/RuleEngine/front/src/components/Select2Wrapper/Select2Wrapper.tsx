import React, { useEffect, useRef } from 'react';
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
  closeTick?: boolean;
};

type Props = Select2GlobalProps & {
  data?: (Select2Option | Select2OptionGroup)[];
  multiple: boolean;
  ajax?: Select2Ajax;
  onValueChange?: (value: Select2Value | Select2Value[]) => void;
  value?: Select2Value | Select2Value[];
};

const Select2Wrapper: React.FC<Props> = ({
  hiddenLabel = false,
  id,
  label,
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
  value,
  onValueChange,
  closeTick = false,
  hideSearch = false,
}) => {
  const select2ref = useRef<HTMLInputElement | null>(null);

  const getSelect2Input: () => any = () => {
    return $(select2ref.current as any) as any;
  };

  const initSelect2 = (destroy = false) => {
    if (select2ref.current) {
      if (destroy) {
        getSelect2Input().select2('close');
        getSelect2Input().select2('destroy');
      }
      getSelect2Input().val(value);
      const options: any = {
        ajax,
        data,
        multiple,
        initSelection,
        placeholder,
        formatSelection,
        formatResult,
        containerCssClass,
        dropdownCssClass,
        hideSearch,
      };
      if (hideSearch) {
        options.minimumResultsForSearch = Infinity;
      }
      getSelect2Input().select2(options);

      if (onSelecting) {
        getSelect2Input().off('select2-selecting');
        getSelect2Input().on('select2-selecting', onSelecting);
      }

      if (onValueChange) {
        getSelect2Input().on('change', (e: Select2Event) => {
          const val = e.val;
          onValueChange(
            Array.isArray(val) ? (val as Select2Value[]) : (val as Select2Value)
          );
        });
      }
    }
  };

  useEffect(() => {
    initSelect2();

    return () => {
      getSelect2Input().off('change');
      getSelect2Input().select2('destroy');
      getSelect2Input()
        .select2('container')
        .remove();
    };
  }, [select2ref]);

  useEffect(() => {
    if (select2ref.current) {
      getSelect2Input()
        .val(value)
        .trigger('change.select2');
    }
  }, [value]);

  useEffect(() => {
    initSelect2(true);
  }, [JSON.stringify(data)]);

  useEffect(() => {
    initSelect2(true);
  }, [onSelecting]);

  useEffect(() => {
    if (select2ref.current) {
      getSelect2Input().select2('close');
    }
  }, [closeTick]);

  return (
    <>
      <Label label={label} hiddenLabel={hiddenLabel} htmlFor={id} />
      <input id={id} type='hidden' ref={select2ref} />
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
