import React from 'react';
import { ProductIdentifier, ProductModelCode } from '../../../../../models';
import {
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  ActionTitle,
} from '../ActionLine';
import { Label } from '../../../../../components/Labels';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { AssociationTypeSwitcher } from './AssociationTypeSwitcher';
import { QuantifiedAssociationValue } from '../../../../../models/actions/SetQuantifiedAssociationsAction';
import {
  QuantifiedAssociationTarget,
  QuantifiedTarget,
} from '../SetQuantifiedAssociationsActionLine';
import { QuantifiedAssociationsIdentifiersSelector } from './QuantifiedAssociationsIdentifiersSelector';

type Props = {
  value: QuantifiedAssociationValue;
  onChange: (value: QuantifiedAssociationValue) => void;
  hasError?: boolean;
};

const QuantifiedAssociationTypesSelector: React.FC<Props> = ({
  value,
  onChange,
  hasError,
}) => {
  const translate = useTranslate();

  const [associationValues, setAssociationValues] = React.useState<
    Map<
      QuantifiedAssociationTarget,
      | { identifier: ProductIdentifier; quantity: number }[]
      | { identifier: ProductModelCode; quantity: number }[]
    >
  >();
  const [
    currentAssociationTarget,
    setCurrentAssociationTarget,
  ] = React.useState<QuantifiedAssociationTarget>();

  React.useEffect(() => {
    const associationValuesArray: any = [];
    Object.keys(value || {}).forEach((associationTypeCode: string) => {
      (['products', 'product_models'] as QuantifiedTarget[]).forEach(target => {
        if (typeof value[associationTypeCode][target] !== 'undefined') {
          associationValuesArray.push([
            { associationTypeCode, target },
            value[associationTypeCode][target],
          ]);
        }
      });
    });
    const associationValues = new Map<
      QuantifiedAssociationTarget,
      | { identifier: ProductIdentifier; quantity: number }[]
      | { identifier: ProductModelCode; quantity: number }[]
    >(associationValuesArray);
    setAssociationValues(associationValues);
    setCurrentAssociationTarget(Array.from(associationValues.keys())[0]);
  }, []);

  if (typeof associationValues === 'undefined') {
    return (
      <img
        src='/bundles/pimui/images//loader-V2.svg'
        alt={translate('pim_common.loading')}
      />
    );
  }

  const currentAssociationTargetOrDefault: () =>
    | QuantifiedAssociationTarget
    | undefined = () => {
    if (!currentAssociationTarget) {
      return Array.from(associationValues.keys())[0];
    }
    if (
      Array.from(associationValues.keys()).some(
        associationTarget =>
          associationTarget.associationTypeCode ===
            currentAssociationTarget.associationTypeCode &&
          associationTarget.target === currentAssociationTarget.target
      )
    ) {
      return currentAssociationTarget;
    }
    return Array.from(associationValues.keys())[0];
  };

  const formatAssociationValues = () => {
    return Array.from(associationValues.entries()).reduce(
      (result, [associationTarget, value]) => {
        if (!result[associationTarget.associationTypeCode]) {
          result[associationTarget.associationTypeCode] = {};
        }
        result[associationTarget.associationTypeCode][
          associationTarget.target
        ] = value;
        return result;
      },
      {} as QuantifiedAssociationValue
    );
  };

  const handleAssociationTargetDelete = (
    associationTarget: QuantifiedAssociationTarget
  ) => {
    associationValues.delete(associationTarget);
    setAssociationValues(new Map(associationValues));
    setCurrentAssociationTarget({ ...Array.from(associationValues.keys())[0] });
    onChange(formatAssociationValues());
  };

  const handleChange = (
    associationTarget: QuantifiedAssociationTarget,
    value:
      | { identifier: ProductIdentifier; quantity: number }[]
      | { identifier: ProductModelCode; quantity: number }[]
  ) => {
    associationValues.set(associationTarget, value);
    setAssociationValues(new Map(associationValues));
    onChange(formatAssociationValues());
  };

  const handleAddAssociationType = (
    associationTarget: QuantifiedAssociationTarget
  ) => {
    associationValues.set(associationTarget, []);
    setAssociationValues(new Map(associationValues));
    setCurrentAssociationTarget(associationTarget);
    onChange(formatAssociationValues());
  };

  return (
    <ActionGrid>
      <ActionLeftSide>
        <ActionTitle>
          {translate(
            'pimee_catalog_rule.form.edit.actions.set_associations.select_association_type'
          )}
        </ActionTitle>
        <Label
          className='AknFieldContainer-label control-label'
          label={`${translate(
            'pimee_catalog_rule.form.edit.actions.set_associations.association_types'
          )} ${translate('pim_common.required_label')}`}
        />
        <AssociationTypeSwitcher
          associationValues={associationValues}
          handleAssociationTargetDelete={target =>
            handleAssociationTargetDelete(target as QuantifiedAssociationTarget)
          }
          handleAddAssociationType={target =>
            handleAddAssociationType(target as QuantifiedAssociationTarget)
          }
          setCurrentAssociationTarget={target =>
            setCurrentAssociationTarget(target as QuantifiedAssociationTarget)
          }
          currentAssociationTarget={currentAssociationTargetOrDefault()}
          quantified={true}
          hasError={!!hasError}
        />
      </ActionLeftSide>
      <ActionRightSide>
        {currentAssociationTargetOrDefault() && (
          <>
            <ActionTitle>
              {translate(
                `pimee_catalog_rule.form.edit.actions.set_associations.select_title.${
                  currentAssociationTargetOrDefault()?.target
                }`
              )}
            </ActionTitle>
            <Label
              className='AknFieldContainer-label control-label'
              label={`${translate(
                `pimee_catalog_rule.form.edit.actions.set_associations.select.${
                  currentAssociationTargetOrDefault()?.target
                }`
              )} ${translate('pim_common.required_label')}`}
            />
            {(currentAssociationTargetOrDefault() as QuantifiedAssociationTarget)
              .target === 'products' && (
              <QuantifiedAssociationsIdentifiersSelector
                entityType='product'
                value={
                  (associationValues.get(
                    currentAssociationTargetOrDefault() as QuantifiedAssociationTarget
                  ) as { identifier: ProductIdentifier; quantity: number }[]) ||
                  []
                }
                onChange={productIdentifiers =>
                  handleChange(
                    currentAssociationTargetOrDefault() as QuantifiedAssociationTarget,
                    productIdentifiers
                  )
                }
              />
            )}
            {(currentAssociationTargetOrDefault() as QuantifiedAssociationTarget)
              .target === 'product_models' && (
              <QuantifiedAssociationsIdentifiersSelector
                entityType='product_model'
                value={
                  (associationValues.get(
                    currentAssociationTargetOrDefault() as QuantifiedAssociationTarget
                  ) as { identifier: ProductModelCode; quantity: number }[]) ||
                  []
                }
                onChange={productModelCodes =>
                  handleChange(
                    currentAssociationTargetOrDefault() as QuantifiedAssociationTarget,
                    productModelCodes
                  )
                }
              />
            )}
          </>
        )}
      </ActionRightSide>
    </ActionGrid>
  );
};

export { QuantifiedAssociationTypesSelector };
