import React, {useState, useEffect} from 'react';
import {Field, SelectInput, TextAreaInput} from 'akeneo-design-system';
import {TextField, useRoute, useMediator, Section, useTranslate} from '@akeneo-pim-community/shared';

type RemoteStorageTabProps = {
  jobInstanceCode: string;
};

type RemoteStoragePasswordLogin = {
  type: 'password';
  password: string;
};

type RemoteStoragePrivateKeyLogin = {
  type: 'private_key';
  private_key: string;
  passphrase: string;
};

type RemoteStorageLogin = RemoteStoragePasswordLogin | RemoteStoragePrivateKeyLogin;

type RemoteStorageLoginType = RemoteStorageLogin['type'];

type RemoteStorage = {
  job_instance_code: string;
  host: string;
  port: number;
  root: string;
  username: string;
  login: RemoteStorageLogin;
};

const getDefaultRemoteStoragePasswordLogin = (): RemoteStoragePasswordLogin => ({
  type: 'password',
  password: '',
});

const getDefaultRemoteStoragePrivateKeyLogin = (): RemoteStoragePrivateKeyLogin => ({
  type: 'private_key',
  private_key: '',
  passphrase: '',
});

const getDefaultRemoteStorage = (): Omit<RemoteStorage, 'job_instance_code'> => ({
  host: '',
  port: 22,
  root: '',
  username: '',
  login: getDefaultRemoteStoragePasswordLogin(),
});

const isRemoteStoragePasswordLogin = (login: RemoteStorageLogin): login is RemoteStoragePasswordLogin =>
  login.type === 'password';

const isRemoteStoragePrivateKeyLogin = (login: RemoteStorageLogin): login is RemoteStoragePrivateKeyLogin =>
  login.type === 'private_key';

const RemoteStorageTab = ({jobInstanceCode}: RemoteStorageTabProps) => {
  const getRoute = useRoute('akeneo_job_get_job_instance_remote_storage_action', {job_instance_code: jobInstanceCode});
  const saveRoute = useRoute('akeneo_job_save_job_instance_remote_storage_action');
  const mediator = useMediator();
  const translate = useTranslate();
  const [remoteStorage, setRemoteStorage] = useState<RemoteStorage>({
    ...getDefaultRemoteStorage(),
    job_instance_code: jobInstanceCode,
  });

  const handleLoginTypeChange = (loginType: RemoteStorageLoginType) => {
    let login;

    switch (loginType) {
      case 'password':
        login = getDefaultRemoteStoragePasswordLogin();
        break;
      case 'private_key':
        login = getDefaultRemoteStoragePrivateKeyLogin();
        break;
      default:
        throw new Error('Unknown login type');
    }

    setRemoteStorage({
      ...remoteStorage,
      login,
    });
  };

  const saveRemoteStorage = async () => {
    await fetch(saveRoute, {
      method: 'POST',
      body: JSON.stringify(remoteStorage),
    });
  };

  useEffect(() => {
    const fetchRemoteStorage = async () => {
      const response = await fetch(getRoute);
      const data = await response.json();

      if(data.length !== 0) {
        setRemoteStorage(data);
      }
    };

    void fetchRemoteStorage();
  }, [getRoute, saveRoute]);

  useEffect(() => {
    mediator.on('job_server_credentials:pre_save', saveRemoteStorage);

    return () => {mediator.off('job_server_credentials:pre_save', saveRemoteStorage)};
  }, [remoteStorage]);

  return (
    <Section>
      <TextField
        label="Host"
        value={remoteStorage.host}
        onChange={(host: string) => setRemoteStorage(remoteStorage => ({...remoteStorage, host}))}
      />
      <TextField
        label="Port"
        value={remoteStorage.port.toString()}
        onChange={(port: string) => setRemoteStorage(remoteStorage => ({...remoteStorage, port: parseInt(port, 10)}))}
      />
      <TextField
        label="Root"
        value={remoteStorage.root}
        onChange={(root: string) => setRemoteStorage(remoteStorage => ({...remoteStorage, root}))}
      />
      <TextField
        label="Username"
        value={remoteStorage.username}
        onChange={(username: string) => setRemoteStorage(remoteStorage => ({...remoteStorage, username}))}
      />
      <Field label="Login type">
        <SelectInput
          value={remoteStorage.login.type}
          onChange={handleLoginTypeChange}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          clearable={false}
        >
            <SelectInput.Option value="password">
              Password
            </SelectInput.Option>
            <SelectInput.Option value="private_key">
              Private key
            </SelectInput.Option>
        </SelectInput>
      </Field>
      {isRemoteStoragePasswordLogin(remoteStorage.login) && (
        <TextField
          label="Password"
          type="password"
          value={remoteStorage.login.password}
          onChange={(password: string) => setRemoteStorage(remoteStorage => ({...remoteStorage, login: {...remoteStorage.login, password}}))}
        />
      )}
      {isRemoteStoragePrivateKeyLogin(remoteStorage.login) && (
        <>
          <Field label="Private key">
            <TextAreaInput
              value={remoteStorage.login.private_key}
              onChange={(private_key: string) => setRemoteStorage(remoteStorage => ({...remoteStorage, login: {...remoteStorage.login, private_key}}))}
            />
          </Field>
          <TextField
            label="Passphrase"
            type="password"
            value={remoteStorage.login.passphrase}
            onChange={(passphrase: string) => setRemoteStorage(remoteStorage => ({...remoteStorage, login: {...remoteStorage.login, passphrase}}))}
          />
        </>
      )}
    </Section>
  );
};

export {RemoteStorageTab};
export type {RemoteStorageTabProps};
