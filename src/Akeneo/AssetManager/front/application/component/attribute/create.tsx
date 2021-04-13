import React, {useRef} from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import {AssetsIllustration, Checkbox, Button, useAutoFocus, Modal, Field, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {getErrorsForPath, Section, TextField} from '@akeneo-pim-community/shared';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {
  attributeCreationCodeUpdated,
  attributeCreationLabelUpdated,
  attributeCreationCancel,
  attributeCreationTypeUpdated,
  attributeCreationValuePerLocaleUpdated,
  attributeCreationValuePerChannelUpdated,
} from 'akeneoassetmanager/domain/event/attribute/create';
import {createAttribute} from 'akeneoassetmanager/application/action/attribute/create';
import {getAttributeTypes} from 'akeneoassetmanager/application/configuration/attribute';

const AttributeTypeIcon = styled.img`
  width: 25px;
  height: 25px;
  margin-right: 10px;
`;

interface StateProps {
  context: {
    locale: string;
  };
  data: {
    code: string;
    labels: {
      [localeCode: string]: string;
    };
    type: string;
    value_per_locale: boolean;
    value_per_channel: boolean;
  };
  errors: ValidationError[];
}

interface DispatchProps {
  events: {
    onCodeUpdated: (value: string) => void;
    onLabelUpdated: (value: string, locale: string) => void;
    onTypeUpdated: (type: string) => void;
    onValuePerLocaleUpdated: (valuePerLocale: boolean) => void;
    onValuePerChannelUpdated: (valuePerChannel: boolean) => void;
    onCancel: () => void;
    onSubmit: () => void;
  };
}

interface CreateProps extends StateProps, DispatchProps {}

const Create = ({data, errors, context, events}: CreateProps) => {
  const translate = useTranslate();
  const labelInputRef = useRef<HTMLInputElement>(null);
  const attributeTypes = getAttributeTypes();

  const handleLabelChange = (value: string) => events.onLabelUpdated(value, context.locale);

  useAutoFocus(labelInputRef);

  return (
    <Modal illustration={<AssetsIllustration />} onClose={events.onCancel} closeTitle={translate('pim_common.close')}>
      <Modal.SectionTitle color="brand">{translate('pim_asset_manager.attribute.create.subtitle')}</Modal.SectionTitle>
      <Modal.Title>{translate('pim_asset_manager.attribute.create.title')}</Modal.Title>
      <Section>
        {translate('pim_asset_manager.attribute.create.description')}
        <TextField
          locale={context.locale}
          ref={labelInputRef}
          label={translate('pim_asset_manager.attribute.create.input.label')}
          value={data.labels[context.locale] ?? ''}
          onChange={handleLabelChange}
          errors={getErrorsForPath(errors, 'labels')}
          onSubmit={events.onSubmit}
        />
        <TextField
          label={translate('pim_asset_manager.attribute.create.input.code')}
          value={data.code}
          onChange={events.onCodeUpdated}
          errors={getErrorsForPath(errors, 'code')}
          onSubmit={events.onSubmit}
        />
        <Field label={translate('pim_asset_manager.attribute.create.input.type')}>
          <SelectInput
            emptyResultLabel={translate('pim_asset_manager.result_counter', {count: 0}, 0)}
            clearable={false}
            value={data.type ?? attributeTypes[0]}
            onChange={events.onTypeUpdated}
          >
            {attributeTypes.map(({identifier, icon, label}) => (
              <SelectInput.Option key={identifier} title={translate(label)} value={identifier}>
                <AttributeTypeIcon src={icon} />
                {translate(label)}
              </SelectInput.Option>
            ))}
          </SelectInput>
          {getErrorsView(errors, 'type')}
        </Field>
        <div>
          <Checkbox checked={data.value_per_channel} onChange={events.onValuePerChannelUpdated}>
            {translate('pim_asset_manager.attribute.create.input.value_per_channel')}
          </Checkbox>
          {getErrorsView(errors, 'valuePerChannel')}
        </div>
        <div>
          <Checkbox checked={data.value_per_locale} onChange={events.onValuePerLocaleUpdated}>
            {translate('pim_asset_manager.attribute.create.input.value_per_locale')}
          </Checkbox>
          {getErrorsView(errors, 'valuePerLocale')}
        </div>
      </Section>
      <Modal.BottomButtons>
        <Button onClick={events.onSubmit}>{translate('pim_common.save')}</Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export default connect(
  (state: EditState): StateProps => {
    return {
      data: state.createAttribute.data,
      errors: state.createAttribute.errors,
      context: {
        locale: state.user.catalogLocale,
      },
    } as StateProps;
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onLabelUpdated: (value: string, locale: string) => {
          dispatch(attributeCreationLabelUpdated(value, locale));
        },
        onCodeUpdated: (value: string) => {
          dispatch(attributeCreationCodeUpdated(value));
        },
        onTypeUpdated: (value: string) => {
          dispatch(attributeCreationTypeUpdated(value));
        },
        onValuePerLocaleUpdated: (valuePerLocale: boolean) => {
          dispatch(attributeCreationValuePerLocaleUpdated(valuePerLocale));
        },
        onValuePerChannelUpdated: (valuePerChannel: boolean) => {
          dispatch(attributeCreationValuePerChannelUpdated(valuePerChannel));
        },
        onCancel: () => {
          dispatch(attributeCreationCancel());
        },
        onSubmit: () => {
          dispatch(createAttribute());
        },
      },
    } as DispatchProps;
  }
)(Create);
