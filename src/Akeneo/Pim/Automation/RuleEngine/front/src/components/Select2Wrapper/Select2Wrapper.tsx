import $ from 'jquery';
import React, { useRef, useEffect } from 'react';
import { Label } from '../Labels';

type option = { id: number | string; text: string };

type Select2Event = {
  val: any;
} & JQuery.Event;

type Props = {
  allowClear?: boolean;
  containerCssClass?: string;
  data: option[];
  dropdownCssClass?: string;
  formatResult?: (item: { id: string }) => string;
  hiddenLabel?: boolean;
  id: string;
  label: string;
  onChange: (event: Select2Event) => void;
  placeholder?: string;
  value: string;
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
    });
    $select.on('change', (event: Select2Event) => onChange(event));
    return () => {
      $select.off('change');
      $select.select2('destroy');
      $select.select2('container').remove();
    };
  }, [
    allowClear,
    containerCssClass,
    data,
    dropdownCssClass,
    formatResult,
    onChange,
    placeholder,
    select2Ref,
    value,
  ]);
  return (
    <>
      <Label label={label} hiddenLabel={hiddenLabel} htmlFor={id} />
      <input id={id} type='hidden' ref={select2Ref} />
    </>
  );
};

export { Select2Wrapper };
