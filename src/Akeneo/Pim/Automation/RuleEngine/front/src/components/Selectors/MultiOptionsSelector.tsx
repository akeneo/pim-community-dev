import React from 'react';
import { Select2MultiAsyncWrapper, Select2Option, Select2Value } from "../Select2Wrapper";
import { Router } from "../../dependenciesTools";
import { httpGet } from "../../fetch";

type Props = {
  id: string;
  label: string;
  hiddenLabel: boolean;
  router: Router;
  currentCatalogLocale: string;
  collectionId: number;
  onValueChange: (value: Select2Value[]) => void;
  value: string[];
};

const MultiOptionsSelector: React.FC<Props> = ({
  id,
  label,
  hiddenLabel = false,
  router,
  currentCatalogLocale,
  collectionId,
  onValueChange,
  value,
}) => {
  const dataProvider = (term: string, page?: number) => {
    const options: any = {
      type: 'code',
      locale: currentCatalogLocale,
    };
    if (page) {
      options.page = page;
      options.limit = 20;
    }
    return {
      class: 'Akeneo\\Pim\\Structure\\Component\\Model\\AttributeOption',
      dataLocale: currentCatalogLocale,
      collectionId: collectionId,
      search: term,
      options: options,
    };
  };

  const handleResults = (
    response: { results: Select2Option[] },
  ) => {
    return {
      more: 20 === response.results.length,
      results: response.results
    };
  };

  const initSelectedOptions = (
    value: any, callback: any
  ) => {
    let dataParams = dataProvider('');
    dataParams = { ...dataParams, options: { ...dataParams.options, ids: value } };
    const toto = router.generate('pim_ui_ajaxentity_list', dataParams);
    httpGet(toto).then((tata) => {
      tata.json().then((json: any) => {
        callback(json.results);
      })
    });
  };

  return <Select2MultiAsyncWrapper
    id={id}
    label={label}
    ajax={{
      url: router.generate('pim_ui_ajaxentity_list'),
      quietMillis: 250,
      cache: true,
      data: dataProvider,
      results: handleResults,
    }}
    value={value}
    initSelection={(_element, callback) => {
      initSelectedOptions(value, callback);
    }}
    onValueChange={onValueChange}
    hiddenLabel={hiddenLabel}
  />
};


export { MultiOptionsSelector }
