import React, {FunctionComponentElement, useState} from 'react';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {useAttributeCodeInput} from '../hooks/attributes/useAttributeCodeInput';
import {
  AttributesIllustration,
  Button,
  Checkbox,
  Field,
  Helper,
  Link,
  Locale,
  Modal,
  TextInput,
  Tooltip,
  useAutoFocus,
} from 'akeneo-design-system';
import styled from 'styled-components';
import {CheckBoxesContainer} from './styles';

const FieldSet = styled.div`
  & > * {
    margin-top: 20px;
  }
`;
type CreateAttributeModalExtraFieldProps = {};
type CreateAttributeModalExtraField = {
  data: {[key: string]: any};
  component: FunctionComponentElement<CreateAttributeModalExtraFieldProps>;
  valid: boolean;
};
type AttributeType = string;
type AttributeData = {
  attribute_type?: AttributeType;
} & {[key: string]: any};

type CreateAttributeModalProps = {
  onClose: () => void;
  onStepConfirm: (data: AttributeData) => void;
  initialData?: AttributeData;
  extraFields?: CreateAttributeModalExtraField[];
  onBack?: () => void;
  children?: React.ReactNode;
};

const CreateAttributeModal: React.FC<CreateAttributeModalProps> = ({
  onClose,
  onStepConfirm,
  initialData,
  extraFields = [],
  onBack,
  children,
}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const [label, setLabel] = React.useState<string>(initialData?.label || '');
  const [code, CodeField, isCodeValid] = useAttributeCodeInput({
    defaultCode: initialData?.code,
    generatedFromLabel: label,
  });
  const [isUniqueValue, setIsUniqueValue] = useState(initialData?.attribute_type === 'pim_catalog_identifier');
  const [isScopable, setIsScopable] = useState(false);
  const [isLocalizable, setIsLocalizable] = useState(false);

  const labelRef: React.RefObject<HTMLInputElement> = React.createRef();

  const handleConfirm = () => {
    const extraFieldsData = extraFields.reduce((old, extraField: CreateAttributeModalExtraField) => {
      return {...old, ...extraField.data};
    }, {} as {[key: string]: any});
    onStepConfirm({code, label, isUniqueValue, isScopable, isLocalizable, ...extraFieldsData});
  };

  useAutoFocus(labelRef);

  const handleBack = () => {
    onBack?.();
  };

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onClose} illustration={<AttributesIllustration />}>
      <Modal.TopLeftButtons>
        <Button level={'tertiary'} onClick={handleBack}>
          {translate('pim_common.previous')}
        </Button>
      </Modal.TopLeftButtons>
      <Modal.SectionTitle color="brand">
        {translate('pim_enrich.entity.attribute.module.create.button')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('pim_common.create')}</Modal.Title>
      {initialData?.attribute_type === 'pim_catalog_identifier' && (
        <Helper level="info">
          {translate('pim_enrich_attribute_form.identifiers_limit')}
          <Link href="https://help.akeneo.com/serenity-build-your-catalog/33-serenity-manage-your-product-identifiers">
            {translate('pim_enrich_attribute_form.identifiers_limit_link')}
          </Link>
        </Helper>
      )}
      <FieldSet>
        <Field label={translate('pim_common.label')} locale={<Locale code={userContext.get('catalogLocale')} />}>
          <TextInput
            ref={labelRef}
            value={label}
            onChange={setLabel}
            maxLength={100}
            characterLeftLabel={translate(
              'pim_common.characters_left',
              {count: 100 - label.length},
              100 - label.length
            )}
          />
        </Field>
        {CodeField}
        {extraFields.map((field: CreateAttributeModalExtraField, i: number) =>
          React.cloneElement<CreateAttributeModalExtraFieldProps>(field.component, {key: i})
        )}
        <CheckBoxesContainer>
          <Checkbox
            readOnly={initialData?.attribute_type === 'pim_catalog_identifier'}
            checked={isUniqueValue}
            onChange={setIsUniqueValue}
          >
            {translate('pim_enrich.entity.attribute.property.unique')}
          </Checkbox>
          <Tooltip direction="top">
            <b>{translate('pim_enrich.entity.attribute.property.unique')}</b>
            <p>{translate('pim_enrich.entity.attribute.property.unique_value_helper')}</p>
          </Tooltip>
        </CheckBoxesContainer>
        <CheckBoxesContainer>
          <Checkbox checked={isScopable} onChange={setIsScopable}>
            {translate('pim_enrich.entity.attribute.property.scopable')}
          </Checkbox>
          <Tooltip direction="top">
            <b>{translate('pim_enrich.entity.attribute.property.scopable')}</b>
            <p>{translate('pim_enrich.entity.attribute.property.scopable_helper')}</p>
          </Tooltip>
        </CheckBoxesContainer>
        <CheckBoxesContainer>
          <Checkbox checked={isLocalizable} onChange={setIsLocalizable}>
            {translate('pim_enrich.entity.attribute.property.localizable')}
          </Checkbox>
          <Tooltip direction="top">
            <b>{translate('pim_enrich.entity.attribute.property.localizable')}</b>
            <p>{translate('pim_enrich.entity.attribute.property.localizable_helper')}</p>
          </Tooltip>
        </CheckBoxesContainer>
      </FieldSet>
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={onClose}>
          {translate('pim_common.cancel')}
        </Button>
        <Button
          level="primary"
          onClick={handleConfirm}
          disabled={!isCodeValid || extraFields.some(field => !field.valid)}
        >
          {translate('pim_common.confirm')}
        </Button>
      </Modal.BottomButtons>
      {children}
    </Modal>
  );
};

export {CreateAttributeModal};
