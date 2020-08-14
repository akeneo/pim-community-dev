import React from 'react';
import { Select2Wrapper } from '../Select2Wrapper';
import { useBackboneRouter, useTranslate } from '../../dependenciesTools/hooks';
import { AttributeType } from '../../models/Attribute';

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
  handleAddField: (fieldCode: string) => void;
  isFieldAlreadySelected: (fieldCode: string) => boolean;
  filterSystemFields: string[];
  filterAttributeTypes: AttributeType[];
  containerCssClass?: string;
  dropdownCssClass?: string;
  placeholder: string;
};

const AddFieldButton: React.FC<Props> = ({
  handleAddField,
  isFieldAlreadySelected,
  filterSystemFields,
  filterAttributeTypes,
  containerCssClass,
  dropdownCssClass,
  placeholder,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const [closeTick, setCloseTick] = React.useState<boolean>(false);

  const dataProvider = (term: string, page: number) => {
    return {
      search: term,
      options: {
        page,
        systemFields: filterSystemFields,
        attributeTypes: filterAttributeTypes,
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
      containerCssClass={containerCssClass}
      dropdownCssClass={dropdownCssClass}
      onSelecting={(event: any) => {
        event.preventDefault();
        setCloseTick(!closeTick);
        handleAddField(event.val);
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
      placeholder={placeholder}
      formatResult={option => {
        return option.text === ''
          ? ''
          : `<span class="${
              isFieldAlreadySelected(option.id as string)
                ? 'active-condition'
                : ''
            }">${option.text}</span>`;
      }}
      closeTick={closeTick}
      multiple={false}
    />
  );
};

export { AddFieldButton };
