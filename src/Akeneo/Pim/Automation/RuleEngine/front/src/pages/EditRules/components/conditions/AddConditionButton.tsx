import React from 'react';
import { ajaxResults, Select2Wrapper } from "../../../../components/Select2Wrapper";
import { Router } from "../../../../dependenciesTools";

type AddConditionAttribute = {
  id: number;
  text: string;
}

type AddConditionGroup = {
  id: number | null;
  text: string;
  children: AddConditionAttribute[];
}

type AddConditionResult = AddConditionGroup[];

const dataProvider = (term: string, page: number) => {
  return {
    search: term,
    options: {
      limit: 20,
      page: page,
      locale: 'en_US',
    },
  };
};

const handleResults = (result: AddConditionResult): ajaxResults => {
  return {
    more: true,
    results: result
  };
};

type Props = {
  router: Router;
  handleAddCondition: (fieldCode: string) => void;
}

const AddConditionButton: React.FC<Props> = ({
  router,
  handleAddCondition
}) => {
  return (
    <Select2Wrapper
      id={'add_conditions'}
      label={'Add conditions'}
      hiddenLabel={true}
      containerCssClass={'foo'}
      onChange={handleAddCondition}
      ajax={{
        url: router.generate('pimee_enrich_rule_definition_get_available_condition_fields'),
        quietMillis: 250,
        cache: true,
        data: dataProvider,
        results: (result: AddConditionResult) => {
          return handleResults(result);
        },
      }}
      initSelection={(_element, callback) => {
        callback({ id: 1234, text: 'Add conditions' });
      }}
      value={1234}
    />
  )
}

export { AddConditionButton };
