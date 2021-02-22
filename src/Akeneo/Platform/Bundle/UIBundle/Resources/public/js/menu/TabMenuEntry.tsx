import React, {FC, useEffect, useState} from 'react';
import {getIcon} from 'akeneo-design-system';

type Props = {
    active: boolean;
    title: string;
    url: string;
    iconName: string;
};

const TabMenuEntry: FC<Props> = ({active, title, url, iconName}) => {
 const [icon, setIcon] = useState<React.ReactElement | null>(null);

    useEffect(() => {
        const iconType = getIcon(iconName);
        if (iconType !== undefined) {
            setIcon(React.createElement(iconType, {title: title, size: 24}));
        }
    }, [iconName, title]);

    const className = ['AknHeader'];

    if (active) {
        className.push('AknHeader-menuItem--active');
    }

    return (
        <a className={className.join(' ')} href={url}>
            <div>{icon}</div>
            {title}
        </a>
    );
};

export {TabMenuEntry};
