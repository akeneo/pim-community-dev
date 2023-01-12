import React from 'react';
import styled from 'styled-components';
import {
  Field,
  Helper,
  NumberInput,
  Button,
  CheckIcon,
  getColor,
  getFontSize,
  SelectInput,
  CopyIcon,
} from 'akeneo-design-system';
import {TextField, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {StorageConfiguratorProps, isSftpStorage, isValidLoginType, STORAGE_LOGIN_TYPES} from './model';
import {useCheckStorageConnection} from '../../hooks/useCheckStorageConnection';
import {useGetPublicKey} from '../../hooks/useGetPublicKey';

const CheckStorageForm = styled.div`
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

const CheckStorageConnection = styled.div`
  display: flex;
  align-items: center;
  gap: 8.5px;
  color: ${getColor('green', 100)};
`;

const CopyableInputContainer = styled.div`
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
`;

const CopyableInput = styled.input`
  width: 100%;
  height: 40px;
  border: 1px solid ${getColor('grey', 80)};
  border-radius: 2px;
  box-sizing: border-box;
  background: ${getColor('grey', 20)};
  color: ${getColor('grey', 100)};
  font-size: ${getFontSize('default')};
  line-height: 40px;
  padding: 0 35px 0 15px;
  outline-style: none;
  cursor: not-allowed;
  overflow: hidden;
  text-overflow: ellipsis;
  &:focus-within {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }

  &::placeholder {
    opacity: 1;
    color: ${getColor('grey', 100)};
  }
`;

const CopyableIcon = styled(CopyIcon)`
  position: absolute;
  right: 0;
  top: 0;
  margin: 12px;
  color: ${getColor('grey', 100)};
  cursor: pointer;
`;

const SftpStorageConfigurator = ({
  jobInstanceCode,
  storage,
  fileExtension,
  validationErrors,
  onStorageChange,
}: StorageConfiguratorProps) => {
  if (!isSftpStorage(storage)) {
    throw new Error(`Invalid storage type "${storage.type}" for sftp storage configurator`);
  }

  const translate = useTranslate();
  const portValidationErrors = filterErrors(validationErrors, '[port]');
  const [isValid, canCheckConnection, checkReliability] = useCheckStorageConnection(jobInstanceCode, storage);
  const publicKey = useGetPublicKey();
  const passwordIsStoredOnServer = storage.login_type === 'password' && storage.password === undefined;

  const canCopyToClipboard = (): boolean => 'clipboard' in navigator;
  const copyToClipboard = (publicKey: string) => canCopyToClipboard() && navigator.clipboard.writeText(publicKey);

  return (
    <>
      <TextField
        required={true}
        value={storage.file_path}
        label={translate('pim_import_export.form.job_instance.storage_form.file_path.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.file_path.placeholder', {
          file_extension: fileExtension,
        })}
        onChange={file_path => onStorageChange({...storage, file_path})}
        errors={filterErrors(validationErrors, '[file_path]')}
      />
      <TextField
        required={true}
        value={storage.host}
        label={translate('pim_import_export.form.job_instance.storage_form.host.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.host.placeholder')}
        onChange={(host: string) => onStorageChange({...storage, host: host})}
        errors={filterErrors(validationErrors, '[host]')}
      />
      <TextField
        required={false}
        value={storage.fingerprint ?? ''}
        label={translate('pim_import_export.form.job_instance.storage_form.fingerprint.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.fingerprint.placeholder')}
        onChange={(fingerprint: string) =>
          onStorageChange({...storage, fingerprint: '' === fingerprint ? undefined : fingerprint})
        }
        errors={filterErrors(validationErrors, '[fingerprint]')}
      >
        <Helper>{translate('pim_import_export.form.job_instance.storage_form.fingerprint.helper')}</Helper>
      </TextField>
      <Field
        label={`${translate('pim_import_export.form.job_instance.storage_form.port.label')} ${translate(
          'pim_common.required_label'
        )}`}
      >
        <NumberInput
          min={1}
          max={65535}
          onChange={(port: string) => onStorageChange({...storage, port: parseInt(port, 10)})}
          invalid={0 < portValidationErrors.length}
          value={storage.port.toString()}
          placeholder={translate('pim_import_export.form.job_instance.storage_form.port.placeholder')}
        />
        {portValidationErrors.map((error, key) => (
          <Helper key={key} level="error" inline={true}>
            {translate(error.messageTemplate, error.parameters, error.plural)}
          </Helper>
        ))}
      </Field>
      <Field label={translate('pim_import_export.form.job_instance.storage_form.login_type.label')}>
        <SelectInput
          value={storage.login_type}
          onChange={login_type => {
            if (isValidLoginType(login_type)) {
              onStorageChange({...storage, login_type});
            }
          }}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          clearable={false}
        >
          {STORAGE_LOGIN_TYPES.map(loginType => (
            <SelectInput.Option value={loginType} key={loginType}>
              {translate(`pim_import_export.form.job_instance.storage_form.login_type.${loginType}`)}
            </SelectInput.Option>
          ))}
        </SelectInput>
      </Field>
      <TextField
        value={storage.username}
        required={true}
        label={translate('pim_import_export.form.job_instance.storage_form.username.label')}
        placeholder={translate('pim_import_export.form.job_instance.storage_form.username.placeholder')}
        onChange={(username: string) => onStorageChange({...storage, username})}
        errors={filterErrors(validationErrors, '[username]')}
      />
      {storage.login_type === 'password' ? (
        <TextField
          actions={passwordIsStoredOnServer && (
            <Button level="secondary" ghost={true} size="small" onClick={() => onStorageChange({...storage, password: ''})}>
              {translate('pim_common.edit')}
            </Button>
          )}
          value={passwordIsStoredOnServer ? '••••••••' : storage.password ?? ''}
          readOnly={passwordIsStoredOnServer}
          required={true}
          type="password"
          label={translate('pim_import_export.form.job_instance.storage_form.password.label')}
          placeholder={translate('pim_import_export.form.job_instance.storage_form.password.placeholder')}
          onChange={(password: string) => onStorageChange({...storage, password})}
          errors={filterErrors(validationErrors, '[password]')}
        />
      ) : (
        <Field label={translate('pim_import_export.form.job_instance.storage_form.public_key.label')}>
          <CopyableInputContainer>
            <CopyableInput disabled={true} data-testid="publicKey" value={publicKey ?? ''} />
            <CopyableIcon
              size={16}
              data-testid="copyToClipboard"
              onClick={() => publicKey && copyToClipboard(publicKey)}
            />
          </CopyableInputContainer>
        </Field>
      )}
      <CheckStorageForm>
        <CheckStorageConnection>
          <Button onClick={checkReliability} disabled={!canCheckConnection} level="primary">
            {translate('pim_import_export.form.job_instance.connection_checker.label')}
          </Button>
          {isValid && <CheckIcon />}
        </CheckStorageConnection>
        {false === isValid && (
          <Helper inline={true} level="error">
            {translate('pim_import_export.form.job_instance.connection_checker.exception')}
          </Helper>
        )}
      </CheckStorageForm>
    </>
  );
};

export {SftpStorageConfigurator};
