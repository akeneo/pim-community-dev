import * as React from 'react';
import {useRef, useEffect} from 'react';
import $ from 'jquery';
import {Family} from '../../../../domain';
const Routing = require('routing');
const i18n = require('pim/i18n');

export interface Props {
  onChange: (value?: any) => void;
  catalogLocale: string;
}

export const FamiliesSelect2 = ({onChange, catalogLocale}: Props) => {
  const ref = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (null === ref.current) {
      return;
    }

    const $select = $(ref.current) as any;
    $select.select2({
      placeholder: ' ',
      allowClear: true,
      dropdownCssClass: 'select2--annotedLabels',
      multiple: true,
      ajax: {
        url: Routing.generate('pim_enrich_family_rest_index'),
        quietMillis: 150,
        cache: true,
        data: (term: string, _: string) => {
          return {
            search: term,
            options: {
              locale: catalogLocale,
              expanded: 0,
            },
          };
        },
        results: (families: Family[]) => {
          const data: any = {
            results: [],
          };

          const sortedFamilies = Object.values(families).sort((family1: any, family2: any) => {
            const family1Label = family1.labels[catalogLocale]
              ? family1.labels[catalogLocale]
              : '[' + family1.code + ']';
            const family2Label = family2.labels[catalogLocale]
              ? family2.labels[catalogLocale]
              : '[' + family2.code + ']';

            return family1Label.localeCompare(family2Label, catalogLocale.replace('_', '-'), {
              sensitivity: 'base',
            });
          });

          Object.values(sortedFamilies).forEach((family: any) => {
            data.results.push({
              id: family.code,
              text: i18n.getLabel(family.labels, catalogLocale, family.code),
            });
          });

          return data;
        },
      },
    });
    $select.on('change', (value: any) => onChange(value || undefined));

    return () => {
      $select.off('change');
      $select.select2('destroy');
    };
  }, [ref]);

  return <input type="hidden" ref={ref} />;
};
