import * as React from 'react';
import {Props as ItemProps} from './BreadcrumbItem';

interface Props {
    children: React.ReactElement<ItemProps> | Array<React.ReactElement<ItemProps>>;
}

export const Breadcrumb = ({children}: Props) => {
    const count = React.Children.count(children);

    return (
        <div className='AknBreadcrumb'>
            {React.Children.map(children, (item, index) => {
                const isLast = item.props.isLast === undefined ? index === count - 1 : item.props.isLast;

                return React.cloneElement(item, {isLast});
            })}
        </div>
    );
};
