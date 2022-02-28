import React, {useState, useEffect} from 'react';
import {Helper, Field, BooleanInput, NumberInput} from 'akeneo-design-system';
import {TextField, useRoute, useMediator, Section} from '@akeneo-pim-community/shared';

type ServerTabProps = {
  jobInstanceCode: string;
};

type ServerCredentials = {
  job_instance_code: string;
  host: string;
  user: string;
  password: string;
  port: number;
  is_secure: boolean;
};

const getDefaultServerCredentials = (): Omit<ServerCredentials, 'job_instance_code'> => ({
  host: '',
  user: '',
  password: '',
  port: 21,
  is_secure: false,
});

const ServerTab = ({jobInstanceCode}: ServerTabProps) => {
  const getRoute = useRoute('akeneo_job_get_job_instance_server_credentials_action', {job_instance_code: jobInstanceCode});
  const saveRoute = useRoute('akeneo_job_save_job_instance_server_credentials_action');
  const mediator = useMediator();
  const [serverCredentials, setServerCredentials] = useState<ServerCredentials>({
    ...getDefaultServerCredentials(),
    job_instance_code: jobInstanceCode,
  });

  const saveServerCredentials = async () => {
    await fetch(saveRoute, {
      method: 'POST',
      body: JSON.stringify(serverCredentials),
    });
  };

  useEffect(() => {
    const fetchServer = async () => {
      const response = await fetch(getRoute);
      const data = await response.json();

      if(data.length !== 0) {
        setServerCredentials(data);
      }
    };

    void fetchServer();
  }, [getRoute, saveRoute]);

  useEffect(() => {
    mediator.on('job_server_credentials:pre_save', saveServerCredentials);

    return () => {mediator.off('job_server_credentials:pre_save', saveServerCredentials)}
  }, [serverCredentials]);

  return (
    <Section>
      <Helper>
        Logoden biniou degemer mat an penn ar, bed Arzhal ul da nor vrec'h ezhomm, bolz us medisin krouiñ davet kreisteiz.
      </Helper>
      <TextField
        label="Host"
        value={serverCredentials.host}
        onChange={(host: string) => setServerCredentials(serverCredentials => ({...serverCredentials, host}))}
      />
      <TextField
        label="User"
        value={serverCredentials.user}
        onChange={(user: string) => setServerCredentials(serverCredentials => ({...serverCredentials, user}))}
      />
      <TextField
        label="Password"
        value={serverCredentials.password}
        onChange={(password: string) => setServerCredentials(serverCredentials => ({...serverCredentials, password}))}
        type="password"
      />
      <Field label="Use secure connection">
        <BooleanInput
          value={serverCredentials.is_secure}
          readOnly={false}
          yesLabel="Yes"
          noLabel="No"
          onChange={(is_secure: boolean) => setServerCredentials(serverCredentials => ({...serverCredentials, is_secure}))}
        />
      </Field>
      <Field label="Port">
        <NumberInput
          value={serverCredentials.port ? serverCredentials.port.toString():'21'}
          readOnly={false}
          onChange={(port: string) => setServerCredentials(serverCredentials => ({...serverCredentials, port: parseInt(port)}))}
        />
      </Field>
    </Section>
  );
};

export {ServerTab};
export type {ServerTabProps};
