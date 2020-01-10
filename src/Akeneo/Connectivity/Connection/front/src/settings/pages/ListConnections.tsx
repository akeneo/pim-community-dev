import React, {useEffect} from 'react';
import {useHistory} from 'react-router';
import {
    ApplyButton,
    Breadcrumb,
    BreadcrumbItem,
    Helper,
    HelperLink,
    HelperTitle,
    PageContent,
    PageHeader,
} from '../../common';
import {PimView} from '../../infrastructure/pim-view/PimView';
import {Connection} from '../../model/connection';
import {FlowType} from '../../model/flow-type.enum';
import {fetchResult} from '../../shared/fetch-result';
import {isOk} from '../../shared/fetch-result/result';
import {BreadcrumbRouterLink, useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {connectionsFetched} from '../actions/connections-actions';
import {ConnectionGrid} from '../components/ConnectionGrid';
import {NoConnection} from '../components/NoConnection';
import {useConnectionsDispatch, useConnectionsState} from '../connections-context';

const MAXIMUM_NUMBER_OF_ALLOWED_CONNECTIONS = 50;

type ResultValue = Array<Connection>;

export const ListConnections = () => {
    const history = useHistory();

    const connections = useConnectionsState();
    const dispatch = useConnectionsDispatch();

    const route = useRoute('akeneo_connectivity_connection_rest_list');
    useEffect(() => {
        fetchResult<ResultValue, never>(route).then(
            result => isOk(result) && dispatch(connectionsFetched(result.value))
        );
    }, [route, dispatch]);

    const handleCreate = () => history.push('/connections/create');

    const breadcrumb = (
        <Breadcrumb>
            <BreadcrumbRouterLink route={'oro_config_configuration_system'}>
                <Translate id='pim_menu.tab.system' />
            </BreadcrumbRouterLink>
            <BreadcrumbItem onClick={() => undefined} isLast={false}>
                <Translate id='pim_menu.item.connection_settings' />
            </BreadcrumbItem>
        </Breadcrumb>
    );

    const userButtons = (
        <PimView
            className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
            viewName='pim-connectivity-connection-user-navigation'
        />
    );

    const createButton = (
        <ApplyButton
            onClick={handleCreate}
            disabled={Object.keys(connections).length >= MAXIMUM_NUMBER_OF_ALLOWED_CONNECTIONS}
            classNames={['AknButtonList-item']}
        >
            <Translate id='pim_common.create' />
        </ApplyButton>
    );

    const sourceConnections = Object.values(connections).filter(
        connection => FlowType.DATA_SOURCE === connection.flowType
    );
    const destinationConnections = Object.values(connections).filter(
        connection => FlowType.DATA_DESTINATION === connection.flowType
    );
    const otherConnections = Object.values(connections).filter(connection => FlowType.OTHER === connection.flowType);

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} buttons={[createButton]} userButtons={userButtons}>
                <Translate id='pim_menu.item.connection_settings' />
            </PageHeader>

            <PageContent>
                <Helper>
                    <HelperTitle>
                        <Translate id='akeneo_connectivity.connection.helper.title' />
                    </HelperTitle>
                    <p>
                        <Translate id='akeneo_connectivity.connection.helper.description' />
                    </p>
                    <HelperLink href='https://help.akeneo.com/pim/articles/what-is-a-connection.html' target='_blank'>
                        <Translate id='akeneo_connectivity.connection.helper.link' />
                    </HelperLink>
                </Helper>

                {Object.keys(connections).length === 0 ? (
                    <NoConnection onCreate={handleCreate} />
                ) : (
                    <>
                        {sourceConnections && sourceConnections.length > 0 && (
                            <ConnectionGrid
                                connections={sourceConnections}
                                title={
                                    <Translate
                                        id='akeneo_connectivity.connection.flow_type.data_source'
                                        count={sourceConnections.length}
                                    />
                                }
                            />
                        )}
                        {destinationConnections && destinationConnections.length > 0 && (
                            <ConnectionGrid
                                connections={destinationConnections}
                                title={
                                    <Translate
                                        id='akeneo_connectivity.connection.flow_type.data_destination'
                                        count={destinationConnections.length}
                                    />
                                }
                            />
                        )}
                        {otherConnections && otherConnections.length > 0 && (
                            <ConnectionGrid
                                connections={otherConnections}
                                title={
                                    <Translate
                                        id='akeneo_connectivity.connection.flow_type.other'
                                        count={otherConnections.length}
                                    />
                                }
                            />
                        )}
                    </>
                )}
            </PageContent>
        </>
    );
};
