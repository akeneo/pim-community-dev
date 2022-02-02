import React, {useEffect, useState} from 'react';
import styled from 'styled-components';
import {
  AkeneoIcon,
  Breadcrumb,
  Button,
  CommonStyle,
  getColor,
  getFontSize,
  ImportXlsxIllustration,
  TabBar,
} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useRoute, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {ImportStructureTab, StructureConfiguration} from './feature';

const JOB_CODE = 'tailoredimport';

const Container = styled.div`
  display: flex;
  width: 100vw;
  height: 100vh;

  ${CommonStyle}
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
    import_structure: StructureConfiguration;
  };
};

const FakePIM = () => {
  const [jobConfiguration, setJobConfiguration] = useState<JobConfiguration | null>(null);
  const [, setValidationErrors] = useState<ValidationError[]>([]);
  const route = useRoute('pim_enrich_job_instance_rest_import_get', {identifier: JOB_CODE});
  const saveRoute = useRoute('pim_enrich_job_instance_rest_import_put', {identifier: JOB_CODE});
  const notify = useNotify();
  const translate = useTranslate();

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
      setValidationErrors([]);

      try {
        const json = await response.json();
        setValidationErrors(json.normalized_errors);
      } catch (error) {}

      notify(NotificationLevel.ERROR, translate('pim_import_export.entity.job_instance.flash.update.fail'));
    } else {
      notify(NotificationLevel.SUCCESS, translate('pim_import_export.entity.job_instance.flash.update.success'));
    }
  };

  useEffect(() => {
    const fetchJobConfiguration = async () => {
      const response = await fetch(route);

      if (404 === response.status) {
        throw new Error(`Be sure to create a Tailored Import job with code "${JOB_CODE}"`);
      }

      const jobConfiguration = await response.json();

      setJobConfiguration(jobConfiguration);
    };

    fetchJobConfiguration();
  }, [route]);

  if (null === jobConfiguration) return null;

  const handleStructureConfigurationChange = (newStructureConfiguration: StructureConfiguration): void => {
    setJobConfiguration({
      ...jobConfiguration,
      configuration: {
        ...jobConfiguration.configuration,
        import_structure: {
          ...jobConfiguration.configuration.import_structure,
          ...newStructureConfiguration,
        },
      },
    });
  };

  return (
    <Container>
      <Menu>
        <AkeneoIcon size={36} />
      </Menu>
      <Page>
        <Header>
          <ImportXlsxIllustration size={138} />
          <div>
            <Breadcrumb>
              <Breadcrumb.Step>Imports</Breadcrumb.Step>
            </Breadcrumb>
            <Title>THIS IS NOT THE PIM</Title>
          </div>
          <SaveButton onClick={saveJobConfiguration}>Save</SaveButton>
        </Header>
        <TabBar moreButtonTitle={translate('pim_common.more')}>
          <TabBar.Tab isActive={false}>Properties</TabBar.Tab>
          <TabBar.Tab isActive={false}>Permissions</TabBar.Tab>
          <TabBar.Tab isActive={false}>Global settings</TabBar.Tab>
          <TabBar.Tab isActive={true}>Import structure</TabBar.Tab>
          <TabBar.Tab isActive={false}>History</TabBar.Tab>
        </TabBar>
        <ImportStructureTab
          structureConfiguration={jobConfiguration.configuration.import_structure}
          onStructureConfigurationChange={handleStructureConfigurationChange}
        />
      </Page>
    </Container>
  );
};

export {FakePIM};
