import React from 'react';
import styled from 'styled-components';
import {PlatformConfiguration} from '../models/PlatformConfiguration';
import {Field, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

type ConnectionProps = {
  configuration: PlatformConfiguration;
  onConfigurationChange: (configuration: PlatformConfiguration) => void;
};
const Connection = ({configuration, onConfigurationChange}: ConnectionProps) => {
  const translate = useTranslate();

  const handleConnectedChannelCodeChange = (connectedChannelCode: string | null) => {
    if (null === connectedChannelCode) return;

    onConfigurationChange({
      ...configuration,
      connection: {
        ...configuration.connection,
        connectedChannelCode,
      },
    });
  };

  return (
    <Container>
      <Field label={translate('Connected channel code')}>
        <TextInput
          onChange={handleConnectedChannelCodeChange}
          value={configuration?.connection?.connectedChannelCode ?? ''}
        />
      </Field>
    </Container>
  );
};

export {Connection};
