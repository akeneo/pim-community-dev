/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import {useRef, useEffect} from 'react';
import * as $ from 'jquery';

export interface Props {
  configuration: {
    placeholder?: string;
    data: Array<{id: string; text: string}>;
    allowClear?: boolean;
    dropdownCssClass?: string;
    formatResult?: (item: {id: string}) => string;
  };
  value?: string;
  onChange: (value?: string) => void;
}

export const Select2 = ({configuration, value, onChange}: Props) => {
  const ref = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (null === ref.current) {
      return;
    }
    const $select = $(ref.current) as any;
    $select.val(value);
    $select.select2(configuration);
    $select.on('change', ({val}: {val: string}) => onChange(val || undefined));

    return () => {
      $select.off('change');
      $select.select2('destroy');
    };
  }, [ref, configuration, value]);

  return <input type='hidden' ref={ref} />;
};
