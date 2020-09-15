import React from 'react';
import {
  Select2Option,
  Select2OptionGroup,
  Select2Wrapper,
} from '../../../../../components/Select2Wrapper';
import {
  useBackboneRouter,
  useTranslate,
  useUserCatalogLocale,
} from '../../../../../dependenciesTools/hooks';
import { AssociationType, AssociationTypeCode } from '../../../../../models';
import { AssociationTarget, Target } from '../SetAssociationsActionLine';
import { getAssociationTypesFromQuantified } from '../../../../../repositories/AssociationTypeRepository';

type Props = {
  onAddAssociationType: (associationTarget: AssociationTarget) => void;
  selectedTargets: AssociationTarget[];
  quantified: boolean;
};

type AssociationTypeSelect2Option = Select2Option & {
  association_type_code: AssociationTypeCode;
  target_text: string;
};

const AddAssociationTypeButton: React.FC<Props> = ({
  onAddAssociationType,
  selectedTargets,
  quantified,
  ...remainingProps
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const currentCatalogLocale = useUserCatalogLocale();
  const targets: Target[] = quantified
    ? ['products', 'product_models']
    : ['products', 'product_models', 'groups'];

  const [closeTick, setCloseTick] = React.useState<boolean>(false);
  const [data, setData] = React.useState<Select2OptionGroup[]>();
  const [associationTypes, setAssociationTypes] = React.useState<
    AssociationType[]
  >();

  const isFieldAlreadySelected = (
    target: Target,
    associationTypeCode: AssociationTypeCode
  ) => {
    return selectedTargets.some(
      associationTarget =>
        associationTarget.associationTypeCode === associationTypeCode &&
        associationTarget.target === target
    );
  };

  React.useEffect(() => {
    getAssociationTypesFromQuantified(
      router,
      quantified
    ).then(associationTypes => setAssociationTypes(associationTypes));
  }, []);

  React.useEffect(() => {
    if (typeof associationTypes === 'undefined') {
      return;
    }

    const data: Select2OptionGroup[] = [];
    associationTypes.forEach(associationType => {
      const children: Select2Option[] = [];
      const text =
        associationType.labels[currentCatalogLocale] ||
        `[${associationType.code}]`;
      targets.forEach((target: Target) => {
        if (!isFieldAlreadySelected(target, associationType.code)) {
          children.push({
            id: target,
            text, // The text is set to the association type label to be able to search on it.
            association_type_code: associationType.code,
            target_text: translate(
              `pimee_catalog_rule.form.edit.actions.set_associations.select.${target}`
            ),
          });
        }
      });
      if (children.length) {
        data.push({
          id: '',
          text,
          children,
        });
      }
    });
    setData(data);
  }, [typeof associationTypes, JSON.stringify(selectedTargets)]);

  const formatResult = (option: AssociationTypeSelect2Option) => {
    return `<span>${option.target_text || option.text}</span>`;
  };

  if (typeof data === 'undefined') {
    return <></>;
  }

  return (
    <Select2Wrapper
      {...remainingProps}
      label={''}
      data={data}
      multiple={false}
      placeholder={translate(
        'pimee_catalog_rule.form.edit.actions.set_associations.add_association_type'
      )}
      hiddenLabel
      dropdownCssClass={'add-association-type-dropdown'}
      onSelecting={e => {
        e.preventDefault();
        const associationTypeCode = e.object?.association_type_code;
        if (typeof associationTypeCode !== 'undefined') {
          onAddAssociationType({
            associationTypeCode: associationTypeCode,
            target: e.val,
          });
          setCloseTick(!closeTick);
        }
      }}
      closeTick={closeTick}
      formatResult={option =>
        formatResult(option as AssociationTypeSelect2Option)
      }
    />
  );
};

export { AddAssociationTypeButton };
