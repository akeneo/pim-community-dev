import {EventSubscriptionLogLevel} from '../model/EventSubscriptionLogLevel';
import {Badge, Level} from 'akeneo-design-system';
import React, {FC, PropsWithChildren} from 'react';

type Props = {
    level: EventSubscriptionLogLevel
};

export const EventLogBadge: FC<PropsWithChildren<Props>> = ({level, children}) => {
    const defineBadgeLevel = (level: EventSubscriptionLogLevel): Level => {
        switch (level) {
            case EventSubscriptionLogLevel.WARNING:
                return 'warning';
            case EventSubscriptionLogLevel.ERROR:
                return 'danger';
            case EventSubscriptionLogLevel.INFO:
                return 'primary';
            case EventSubscriptionLogLevel.NOTICE:
                return 'tertiary';
        }
    };

    return <Badge level={defineBadgeLevel(level)} >{children}</Badge>;
};
