import React, {useState} from 'react';
import styled from 'styled-components';
import {
  AkeneoIcon,
  Breadcrumb,
  Button,
  CommonStyle,
  ExportXlsxIllustration,
  getColor,
  getFontSize,
  TabBar,
  useTabBar,
} from 'akeneo-design-system';
import {CompletenessFilter, CategoryFilter, ColumnsTab} from './feature';
import {useEffect} from 'react';
import {
  NotificationLevel,
  useNotify,
  useRoute,
  useTranslate,
  ValidationError,
  filterErrors,
} from '@akeneo-pim-community/shared';
import {ColumnConfiguration} from './feature/models/ColumnConfiguration';

const JOB_CODE = 'mmm';

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

const FieldContainer = styled.div`
  width: 400px;
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

const SaveButton = styled(Button)`
  position: absolute;
  top: 40px;
  right: 40px;
`;

type JobConfiguration = {
  code: string;
  configuration: {
    filters: {
      data: {
        field: string;
        value: string[];
        operator: string;
        context: any;
      }[];
    };
    columns: ColumnConfiguration[];
  };
};

const FakePIM = () => {
  const [jobConfiguration, setJobConfiguration] = useState<JobConfiguration | null>(null);
  const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);
  const [isCurrent, switchTo] = useTabBar('columns');
  const route = useRoute('pim_enrich_job_instance_rest_export_get', {identifier: JOB_CODE});
  const saveRoute = useRoute('pim_enrich_job_instance_rest_export_put', {identifier: JOB_CODE});
  const notify = useNotify();
  const translate = useTranslate();
  const AVAILABLE_OPERATORS = [
    'ALL',
    'GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE',
    'GREATER OR EQUALS THAN ON ALL LOCALES',
    'LOWER THAN ON ALL LOCALES',
  ];

  const handleColumnConfigurationChange = (columnConfiguration: ColumnConfiguration[]) => {
    if (null !== jobConfiguration) {
      setJobConfiguration(jobConfiguration => ({
        ...jobConfiguration,
        configuration: {...jobConfiguration.configuration, columns: columnConfiguration},
      }));
    }
  };
  const handleCategoryChange = (categoriesSelected: string[]) => {
    if (jobConfiguration === null) return;

    const newFilters = jobConfiguration.configuration.filters.data.map(filter => {
      if (filter.field !== 'categories') return filter;

      return {...filter, operator: categoriesSelected.length === 0 ? 'NOT IN' : 'IN', value: categoriesSelected};
    });

    setJobConfiguration({
      ...jobConfiguration,
      configuration: {...jobConfiguration.configuration, filters: {data: newFilters}},
    });
  };

  const handleFilterChange = newFilter => {
    const newFilters = jobConfiguration.configuration.filters.data.map(filter => {
      if (filter.field !== 'completeness') return filter;

      return newFilter;
    });

    setJobConfiguration({
      ...jobConfiguration,
      configuration: {...jobConfiguration.configuration, filters: {data: newFilters}},
    });
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
      const jobConfiguration = await response.json();

      setJobConfiguration(jobConfiguration);
    };

    fetchJobConfiguration();
  }, [route]);

  if (jobConfiguration === null) return null;
  const categoryFilter = jobConfiguration.configuration.filters.data.find(filter => {
    return filter.field === 'categories';
  });

  const completenessFilter = jobConfiguration.configuration.filters.data.find(filter => {
    return filter.field === 'completeness';
  });

  const categoriesSelected = categoryFilter ? categoryFilter['value'] : [];

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
        <TabBar moreButtonTitle={translate('pim_common.more')}>
          <TabBar.Tab isActive={false}>Properties</TabBar.Tab>
          <TabBar.Tab isActive={false}>Permissions</TabBar.Tab>
          <TabBar.Tab isActive={false}>Global settings</TabBar.Tab>
          <TabBar.Tab isActive={isCurrent('lines')} onClick={() => switchTo('lines')}>
            Filter the data
          </TabBar.Tab>
          <TabBar.Tab isActive={isCurrent('columns')} onClick={() => switchTo('columns')}>
            Select the columns
          </TabBar.Tab>
          <TabBar.Tab isActive={false}>History</TabBar.Tab>
        </TabBar>
        {isCurrent('columns') && (
          <ColumnsTab
            validationErrors={validationErrors}
            columnsConfiguration={jobConfiguration.configuration.columns}
            onColumnsConfigurationChange={handleColumnConfigurationChange}
          />
        )}
        {isCurrent('lines') && (
          <FieldContainer>
            {categoriesSelected.length === 0 ? 'All products' : `${categoriesSelected.length} selected category`}
            <CategoryFilter initialCategorySelection={categoriesSelected} onCategorySelection={handleCategoryChange} />
            <CompletenessFilter
              availableOperators={AVAILABLE_OPERATORS}
              filter={completenessFilter}
              onChange={handleFilterChange}
              validationErrors={filterErrors(validationErrors, '[filters][data][2]')}
            />
          </FieldContainer>
        )}
      </Page>
    </Container>
  );
};

export {FakePIM};
