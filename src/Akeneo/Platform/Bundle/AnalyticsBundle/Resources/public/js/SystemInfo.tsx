import React, {ReactElement} from 'react';
import {PimView, useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {Breadcrumb, Table, Button} from 'akeneo-design-system';
import {useSystemInfo} from './SystemInfoHook';
import styled from 'styled-components';

const TableCell = styled(Table.Cell)`
  white-space: normal;
`;

const TableCellList = styled(Table.Cell)`
  white-space: normal;
  overflow-wrap: break-word;
  div {
    display: block;
  }
`;

const SystemInfo = () => {
  const translate = useTranslate();
  const systemHomeRoute = useRoute('oro_config_configuration_system');
  const downloadTxtRoute = useRoute('pim_analytics_system_info_download');
  const systemInfoData = useSystemInfo();

  const renderArraySystemInfo = (infoValue: any): ReactElement[] =>
    infoValue.map((subInfoValue: any, subInfoKey: string) => {
      return <div key={`${subInfoKey}`}>{renderSystemInfoValue(subInfoValue)}</div>;
    });

  const renderNestedObjectSystemInfo = (infoValue: any, keyPrefix: string = ''): any[] | ReactElement[] =>
    Object.entries(infoValue).map(([subInfoKey, subInfoValue]) => {
      const infoKey = keyPrefix !== '' ? keyPrefix + '.' + subInfoKey : subInfoKey;
      return typeof subInfoValue === 'object' ? (
        renderSystemInfoValue(subInfoValue, subInfoKey)
      ) : (
        <div key={`${infoKey}`}>
          {translate('pim_analytics.info_type.' + infoKey)}: {subInfoValue}
        </div>
      );
    });

  const renderSystemInfoValue = (infoValue: any, keyPrefix: string = ''): any => {
    if (typeof infoValue === 'boolean') {
      return infoValue ? '1' : '0';
    }

    if (Array.isArray(infoValue)) {
      return renderArraySystemInfo(infoValue);
    }

    if (typeof infoValue === 'object') {
      return renderNestedObjectSystemInfo(infoValue, keyPrefix);
    }

    return infoValue;
  };

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={`#${systemHomeRoute}`}>{translate('pim_menu.tab.system')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_analytics.system_info.title')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          <Button href={downloadTxtRoute} level="tertiary" ghost>
            {translate('pim_analytics.system_info.download')}
          </Button>
        </PageHeader.Actions>
        <PageHeader.Title>{translate('pim_analytics.system_info.title')}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <Table>
          <Table.Header>
            {/* @ts-ignore | @fixme: width props definition */}
            <Table.HeaderCell width="35%">{translate('pim_analytics.info_header.property')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_analytics.info_header.information')}</Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            {Object.entries(systemInfoData).map(([systemInfoType, systemInfoValue]) => {
              return (
                <Table.Row key={systemInfoType}>
                  <TableCell>{translate('pim_analytics.info_type.' + systemInfoType)}</TableCell>
                  <TableCellList>{renderSystemInfoValue(systemInfoValue)}</TableCellList>
                </Table.Row>
              );
            })}
          </Table.Body>
        </Table>
      </PageContent>
    </>
  );
};

export {SystemInfo};
