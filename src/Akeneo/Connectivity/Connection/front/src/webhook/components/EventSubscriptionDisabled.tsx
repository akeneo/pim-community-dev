import {ApiIllustration, Link} from 'akeneo-design-system';
import React, {FC} from 'react';
import {generatePath, useHistory} from 'react-router-dom';
import {EmptyState} from '../../common';
import {Translate} from '../../shared/translate';

const EventSubscriptionDisabled: FC<{connectionCode: string}> = ({connectionCode}) => {
    const history = useHistory();

    return (
        <EmptyState.EmptyState>
            <ApiIllustration size={200} />

            <EmptyState.Heading>
                <Translate id='akeneo_connectivity.connection.webhook.event_logs.event_subscription_disabled.title' />
            </EmptyState.Heading>

            <EmptyState.Caption>
                <Link
                    decorated
                    href={history.createHref({
                        pathname: generatePath('/connections/:connectionCode/event-subscription', {connectionCode}),
                    })}
                    target='_self'
                >
                    <Translate id='akeneo_connectivity.connection.webhook.event_logs.event_subscription_disabled.link' />
                </Link>
            </EmptyState.Caption>
        </EmptyState.EmptyState>
    );
};

export {EventSubscriptionDisabled};
