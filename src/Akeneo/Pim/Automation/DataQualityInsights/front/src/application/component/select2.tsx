import * as React from 'react';
import {useRef, useEffect} from 'react';
import $ from 'jquery';

export interface Props {
  configuration: {
    placeholder?: string;
    allowClear?: boolean;
    dropdownCssClass?: string;
    multiple?: boolean,
    data: Array<{id: string; text: string}>;
  };
  onChange: (value?: any) => void;
}

export const Select2 = ({configuration, onChange}: Props) => {
  const ref = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (null === ref.current) {
      return;
    }
    const $select = $(ref.current) as any;
    $select.select2(configuration);
    $select.on('change', (value: any) => onChange(value || undefined));

    return () => {
      $select.off('change');
      $select.select2('destroy');
    };
  }, [ref, configuration]);

  return <input type='hidden' ref={ref} />;
};
