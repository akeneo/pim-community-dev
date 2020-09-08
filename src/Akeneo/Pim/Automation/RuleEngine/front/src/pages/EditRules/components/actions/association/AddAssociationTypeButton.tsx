import React from 'react';
import {
  Select2Option,
  Select2OptionGroup,
  Select2Wrapper,
} from '../../../../../components/Select2Wrapper';
import { getAssociationTypesFromQuantified } from '../../../../../repositories/AssociationTypeRepository';
import {
  useBackboneRouter,
  useTranslate,
  useUserCatalogLocale,
} from '../../../../../dependenciesTools/hooks';
import { AssociationType, AssociationTypeCode } from '../../../../../models';
import { AssociationTarget, Target } from '../SetAssociationsActionLine';

type Props = {
  onAddAssociationType: (
    associationTypeCode: AssociationTypeCode,
    target: Target
  ) => void;
  selectedTargets: AssociationTarget[];
};

type AssociationTypeSelect2Option = Select2Option & {
  association_type_code: AssociationTypeCode;
};

const AddAssociationTypeButton: React.FC<Props> = ({
  onAddAssociationType,
  selectedTargets,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const currentCatalogLocale = useUserCatalogLocale();

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
    getAssociationTypesFromQuantified(router, false).then(associationTypes =>
      setAssociationTypes(associationTypes)
    );
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
      (['products', 'product_models', 'groups'] as Target[]).forEach(
        (target: Target) => {
          if (!isFieldAlreadySelected(target, associationType.code)) {
            children.push({
              id: target,
              text, // The text is set to the association type label to be able to search on it.
              association_type_code: associationType.code,
            });
          }
        }
      );
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
    // If there is no id, this is an option group, else this is a target.
    return `<span>${
      option.id
        ? translate(
            `pimee_catalog_rule.form.edit.actions.set_associations.select.${option.id}`
          )
        : option.text
    }</span>`;
  };

  if (typeof data === 'undefined') {
    return <></>;
  }

  return (
    <Select2Wrapper
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
        const associationTypeCode = e.object.association_type_code;
        if (typeof associationTypeCode !== 'undefined') {
          onAddAssociationType(associationTypeCode, e.val);
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
