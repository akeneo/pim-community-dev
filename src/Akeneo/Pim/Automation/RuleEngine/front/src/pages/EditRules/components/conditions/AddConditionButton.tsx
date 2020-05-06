import React from 'react';
import {
  ajaxResults,
  Select2Wrapper,
} from '../../../../components/Select2Wrapper';
import { Router, Translate } from '../../../../dependenciesTools';

type AddConditionAttribute = {
  id: string;
  text: string;
};

type AddConditionGroup = {
  id: string;
  text: string;
  children: AddConditionAttribute[];
};

type AddConditionResults = AddConditionGroup[];

type Props = {
  router: Router;
  handleAddCondition: (fieldCode: string) => void;
  translate: Translate;
};

const AddConditionButton: React.FC<Props> = ({
  router,
  handleAddCondition,
  translate,
}) => {
  const [ closeTick, setCloseTick ] = React.useState<boolean>(true);

  const dataProvider = (term: string, page: number) => {
    return {
      search: term,
      options: {
        limit: 20,
        page: page,
      },
    };
  };

  let lastDisplayedGroupId: string;
  const handleResults = (result: AddConditionResults): ajaxResults => {
    const firstCurrentGroupId = result[0].id;
    if (firstCurrentGroupId === lastDisplayedGroupId) {
      // Prevents to display 2 times the group label. Having an empty text removes the line.
      result[0].text = '';
    }
    lastDisplayedGroupId = result[result.length - 1].id;

    const fieldCount = result.reduce((previousCount, group) => {
      return previousCount + group.children.length;
    }, 0);

    return {
      more: fieldCount >= 20,
      results: result,
    };
  };

  return (
    <Select2Wrapper
      id={'add_conditions'}
      label={translate('pimee_catalog_rule.form.edit.add_conditions')}
      hiddenLabel={true}
      containerCssClass={'add-conditions-button'}
      dropdownCssClass={'add-conditions-dropdown'}
      onSelecting={(event: any) => {
        event.preventDefault();
        handleAddCondition(event.val);
        setCloseTick(!closeTick);
      }}
      ajax={{
        url: router.generate(
          'pimee_enrich_rule_definition_get_available_condition_fields'
        ),
        quietMillis: 250,
        cache: true,
        data: dataProvider,
        results: (result: AddConditionResults) => {
          return handleResults(result);
        },
      }}
      placeholder={translate('pimee_catalog_rule.form.edit.add_conditions')}
      closeTick={closeTick}
    />
  );
};

export { AddConditionButton };
