import React, {useState} from 'react';
import styled from 'styled-components';
import {
  AkeneoIcon,
  AkeneoThemedProps,
  Breadcrumb,
  Button,
  ExportXlsxIllustration,
  getColor,
  getFontSize,
} from 'akeneo-design-system';
import {ColumnConfiguration, ColumnsTab} from './feature';
import {useEffect} from 'react';
import {NotificationLevel, useNotify, useRoute, useTranslate, ValidationError} from '@akeneo-pim-community/shared';

const JOB_CODE = 'mmm';

const Container = styled.div`
  display: flex;
  width: 100vw;
  height: 100vh;
`;

const Header = styled.div`
  display: flex;
  width: 100%;
  height: 154px;
`;

const Title = styled.div`
  color: ${getColor('brand', 100)};
  font-size: ${getFontSize('title')};
`;

const Tabs = styled.div`
  display: flex;
  width: 100%;
  height: 45px;
  border-bottom: 1px solid ${getColor('grey', 60)};
  gap: 50px;
  font-size: ${getFontSize('big')};
  color: ${getColor('grey', 120)};
`;

const Tab = styled.span<{isActive: boolean} & AkeneoThemedProps>`
  display: flex;
  align-items: center;
  height: 100%;
  color: ${({isActive}) => (isActive ? getColor('brand', 100) : getColor('grey', 120))};
  border-bottom: 2px solid ${({isActive}) => (isActive ? getColor('brand', 100) : 'transparent')};
`;

const Menu = styled.div`
  display: flex;
  justify-content: center;
  padding: 15px;
  width: 80px;
  height: 100vh;
  border-right: 1px solid ${getColor('grey', 60)};
  color: ${getColor('brand', 100)};
`;

const Page = styled.div`
  flex: 1;
  padding: 40px;
`;

const SaveButton = styled(Button)`
  position: absolute;
  top: 40px;
  right: 40px;
`;

type JobConfiguration = {
  code: string;
  configuration: {
    columns: ColumnConfiguration[];
  };
};

const FakePIM = () => {
  const [jobConfiguration, setJobConfiguration] = useState<JobConfiguration | null>(null);
  const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);
  const route = useRoute('pim_enrich_job_instance_rest_export_get', {identifier: JOB_CODE});
  const saveRoute = useRoute('pim_enrich_job_instance_rest_export_put', {identifier: JOB_CODE});
  const notify = useNotify();
  const translate = useTranslate();

  const handleColumnConfigurationChange = (columnConfiguration: ColumnConfiguration[]) => {
    setJobConfiguration(jobConfiguration => ({...jobConfiguration, configuration: {columns: columnConfiguration}}));
  };

  const saveJobConfiguration = async () => {
    setValidationErrors([]);
    const response = await fetch(saveRoute, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({...jobConfiguration, connector: undefined}),
    });

    if (!response.ok) {
      const json = await response.json();

      setValidationErrors(json.normalized_errors);
      notify(NotificationLevel.ERROR, translate('pim_import_export.entity.job_instance.flash.update.fail'));
    } else {
      notify(NotificationLevel.SUCCESS, translate('pim_import_export.entity.job_instance.flash.update.success'));
    }
  };

  useEffect(() => {
    const fetchJobConfiguration = async () => {
      const response = await fetch(route);
      const jobConfiguration = await response.json();

      setJobConfiguration(jobConfiguration);
    };

    fetchJobConfiguration();
  }, [route]);

  return (
    <Container>
      <Menu>
        <AkeneoIcon size={36} />
      </Menu>
      <Page>
        <Header>
          <ExportXlsxIllustration size={138} />
          <div>
            <Breadcrumb>
              <Breadcrumb.Step>Exports</Breadcrumb.Step>
            </Breadcrumb>
            <Title>Tailored Exports</Title>
          </div>
          <SaveButton onClick={saveJobConfiguration}>Save</SaveButton>
        </Header>
        <Tabs>
          <Tab>Properties</Tab>
          <Tab>Permissions</Tab>
          <Tab>Global settings</Tab>
          <Tab>Filter the data</Tab>
          <Tab isActive={true}>Select the columns</Tab>
          <Tab>History</Tab>
        </Tabs>
        {null !== jobConfiguration && (
          <ColumnsTab
            validationErrors={validationErrors}
            columnsConfiguration={jobConfiguration.configuration.columns}
            onColumnsConfigurationChange={handleColumnConfigurationChange}
          />
        )}
      </Page>
    </Container>
  );
};

export {FakePIM};
