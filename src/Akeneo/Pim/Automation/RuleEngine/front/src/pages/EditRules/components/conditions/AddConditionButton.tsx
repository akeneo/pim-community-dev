import React from 'react';
import { ajaxResults, Select2Wrapper } from "../../../../components/Select2Wrapper";
import { Router, Translate } from "../../../../dependenciesTools";

type AddConditionAttribute = {
  id: string;
  text: string;
}

type AddConditionGroup = {
  id: string;
  text: string;
  children: AddConditionAttribute[];
}

type AddConditionResult = AddConditionGroup[];

type Props = {
  router: Router;
  handleAddCondition: (fieldCode: string) => void;
  translate: Translate;
}

const AddConditionButton: React.FC<Props> = ({
  router,
  handleAddCondition,
  translate,
}) => {
  const dataProvider = (term: string, page: number) => {
    return {
      search: term,
      options: {
        limit: 20,
        page: page,
      },
    };
  };

  let lastGroupId: string;
  const handleResults = (result: AddConditionResult): ajaxResults => {
    const firstGroupId = result[0].id;
    if (firstGroupId === lastGroupId) {
      // Prevents to display 2 times the group label.
      result[0].text = '';
    }
    lastGroupId = result[result.length - 1].id;

    const count = result.reduce((previousCount, group) => {
      return previousCount + group.children.length;
    }, 0);

    return {
      more: count >= 20,
      results: result
    };
  };

  return (
    <Select2Wrapper
      id={'add_conditions'}
      label={translate('pimee_catalog_rule.form.edit.add_conditions')}
      hiddenLabel={true}
      containerCssClass={'add-conditions-button'}
      dropdownCssClass={'add-conditions-dropdown'}
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
        callback({ id: 1234, text: translate('pimee_catalog_rule.form.edit.add_conditions') });
      }}
      value={1234}
    />
  )
}

export { AddConditionButton };
