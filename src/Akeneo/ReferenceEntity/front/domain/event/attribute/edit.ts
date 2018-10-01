import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import Attribute, {NormalizableAdditionalProperty} from 'akeneoreferenceentity/domain/model/attribute/attribute';

export const attributeEditionStart = (attribute: Attribute) => {
  return {type: 'ATTRIBUTE_EDITION_START', attribute: attribute.normalize()};
};

export const attributeEditionLabelUpdated = (value: string, locale: string) => {
  return {type: 'ATTRIBUTE_EDITION_LABEL_UPDATED', value, locale};
};

export const attributeEditionIsRequiredUpdated = (is_required: boolean) => {
  return {type: 'ATTRIBUTE_EDITION_IS_REQUIRED_UPDATED', is_required};
};

export const attributeEditionAdditionalPropertyUpdated = (
  propertyCode: string,
  propertyValue: NormalizableAdditionalProperty
) => {
  return {
    type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
    propertyCode,
    propertyValue: propertyValue.normalize(),
  };
};

export const attributeEditionCancel = () => {
  return {type: 'ATTRIBUTE_EDITION_CANCEL'};
};

export const attributeEditionSubmission = () => {
  return {type: 'ATTRIBUTE_EDITION_SUBMISSION'};
};

export const attributeEditionSucceeded = () => {
  return {type: 'ATTRIBUTE_EDITION_SUCCEEDED'};
};

export const attributeEditionErrorOccured = (errors: ValidationError[]) => {
  return {type: 'ATTRIBUTE_EDITION_ERROR_OCCURED', errors};
};
