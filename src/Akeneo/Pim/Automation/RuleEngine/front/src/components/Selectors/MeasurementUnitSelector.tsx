import React from 'react';
import {
  useBackboneRouter,
  useTranslate,
  useUserCatalogLocale,
} from '../../dependenciesTools/hooks';
import {
  Select2Option,
  Select2SimpleSyncWrapper,
  Select2Value,
} from '../Select2Wrapper';
import {
  Attribute,
  LocaleCode,
  MeasurementFamily,
  MeasurementUnitCode,
  MeasurementUnit,
} from '../../models';
import { getMeasurementFamilyByCode } from '../../repositories/MeasurementFamilyRepository';
import { AkeneoSpinner } from '../AkeneoSpinner';
import { HelperContainer, InlineHelper } from '../HelpersInfos/InlineHelper';
import { Router, Translate } from '../../dependenciesTools';

const getUnitLabel = (
  measurementFamily: MeasurementFamily,
  unitCode: MeasurementUnitCode,
  currentCatalogLocale: LocaleCode
): string => {
  const unit = measurementFamily?.units.find(
    (unit: MeasurementUnit) => unit.code === unitCode
  );

  return !measurementFamily || !unit
    ? `[${unitCode}]`
    : unit.labels[currentCatalogLocale] || `[${unit.code}]`;
};

const constructSelect2Data = (
  measurementFamily: MeasurementFamily,
  currentCatalogLocale: LocaleCode
): Select2Option[] => {
  return measurementFamily.units.map((unit: MeasurementUnit) => {
    return {
      id: unit.code,
      text: unit.labels[currentCatalogLocale] || `[${unit.code}]`,
    };
  });
};

const getMeasurementUnitValidator = (
  attribute: Attribute,
  router: Router,
  translate: Translate
) => {
  const validate = async (selectedUnitCode: MeasurementUnitCode) => {
    const measurementFamily = await getMeasurementFamilyByCode(
      attribute.metric_family || '',
      router
    );
    if (null === measurementFamily) {
      return translate(
        'pimee_catalog_rule.exceptions.measurement_family_not_found',
        {
          measurement_code: attribute.metric_family || '',
        }
      );
    }
    if (selectedUnitCode) {
      const unitExists = measurementFamily.units.some(
        (unit: MeasurementUnit) => unit.code === selectedUnitCode
      );
      if (!unitExists) {
        return translate('pimee_catalog_rule.exceptions.unit_not_found', {
          unitCode: selectedUnitCode,
          measurement_code: attribute.metric_family || '',
        });
      }
    }
    return true;
  };

  return { validate };
};

type Props = {
  attribute: Attribute;
  name: string;
  label?: string;
  hiddenLabel?: boolean;
  value: string | null;
  onChange?: (value: string | null) => void;
  placeholder?: string;
};

const MeasurementUnitSelector: React.FC<Props> = ({
  attribute,
  name,
  label,
  hiddenLabel,
  value,
  onChange,
  placeholder,
  ...remainingProps
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const currentCatalogLocale = useUserCatalogLocale();
  const [measurementFamily, setMeasurementFamily] = React.useState<
    MeasurementFamily | null | undefined
  >();
  const handleChange = (value: Select2Value) => {
    if (onChange) {
      onChange(value === null || value === '' ? null : (value as string));
    }
  };

  React.useEffect(() => {
    const updateMeasurement = async () => {
      const measurementFamily = await getMeasurementFamilyByCode(
        attribute.metric_family || '',
        router
      );
      setMeasurementFamily(measurementFamily);
    };
    updateMeasurement();
  }, [attribute]);

  if ('undefined' === typeof measurementFamily) {
    return <AkeneoSpinner />;
  }
  if (null === measurementFamily) {
    return (
      <HelperContainer>
        <InlineHelper danger>
          {translate(
            'pimee_catalog_rule.exceptions.measurement_family_not_found',
            {
              measurement_code: attribute.metric_family || '',
            }
          )}
        </InlineHelper>
      </HelperContainer>
    );
  }

  return (
    <Select2SimpleSyncWrapper
      {...remainingProps}
      dropdownCssClass='measurement-unit-selector-dropdown'
      label={
        label ||
        translate('pimee_catalog_rule.form.edit.fields.measurement_unit')
      }
      hiddenLabel={hiddenLabel}
      value={value}
      onChange={handleChange}
      data={
        measurementFamily
          ? constructSelect2Data(measurementFamily, currentCatalogLocale)
          : []
      }
      placeholder={
        placeholder ||
        translate(
          'pimee_catalog_rule.form.edit.fields.measurement_default_unit',
          {
            unit: measurementFamily
              ? getUnitLabel(
                  measurementFamily,
                  attribute.default_metric_unit || '',
                  currentCatalogLocale
                )
              : attribute.default_metric_unit || '',
          }
        )
      }
      allowClear={true}
      name={name}
    />
  );
};

export { MeasurementUnitSelector, getMeasurementUnitValidator };
