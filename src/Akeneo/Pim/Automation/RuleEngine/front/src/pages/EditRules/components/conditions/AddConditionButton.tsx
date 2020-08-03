import React from 'react';
import { Select2Wrapper } from '../../../../components/Select2Wrapper';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';

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
  handleAddCondition: (fieldCode: string) => void;
  isActiveConditionField: (fieldCode: string) => boolean;
};

// Add here the fields handled by the rule conditions.
// Be sure that the associated UI component exists to display it correctly.
const SYSTEM_FIELDS = ['family', 'categories', 'completeness', 'groups'];

const ATTRIBUTE_TYPES = [
  'pim_catalog_multiselect',
  'pim_catalog_simpleselect',
  'pim_catalog_text',
  'pim_catalog_number',
];

const AddConditionButton: React.FC<Props> = ({
  handleAddCondition,
  isActiveConditionField,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const [closeTick, setCloseTick] = React.useState<boolean>(false);

  const dataProvider = (term: string, page: number) => {
    return {
      search: term,
      options: {
        page,
        systemFields: SYSTEM_FIELDS,
        attributeTypes: ATTRIBUTE_TYPES,
        limit: 20,
      },
    };
  };

  let lastDisplayedGroupLabel: string;
  const handleResults = (result: AddConditionResults) => {
    const fieldCount = result.reduce((previousCount, group) => {
      return previousCount + group.children.length;
    }, 0);

    if (result.length) {
      const firstCurrentGroupLabel = result[0].text;
      if (firstCurrentGroupLabel === lastDisplayedGroupLabel) {
        // Prevents to display 2 times the group label. Having an empty text removes the line.
        result[0].text = '';
      }
      lastDisplayedGroupLabel = result[result.length - 1].text;
    }

    return {
      more: fieldCount >= 20,
      results: result.map(group => {
        return { ...group, disabled: true };
      }),
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
        setCloseTick(!closeTick);
        handleAddCondition(event.val);
      }}
      ajax={{
        url: router.generate(
          'pimee_enrich_rule_definition_get_available_fields'
        ),
        quietMillis: 250,
        cache: true,
        data: dataProvider,
        results: (result: AddConditionResults) => {
          return handleResults(result);
        },
      }}
      placeholder={translate('pimee_catalog_rule.form.edit.add_conditions')}
      formatResult={option => {
        return option.text === ''
          ? ''
          : `<span class="${
              isActiveConditionField(option.id as string)
                ? 'active-condition'
                : ''
            }">${option.text}</span>`;
      }}
      closeTick={closeTick}
      multiple={false}
    />
  );
};

export { AddConditionButton };
