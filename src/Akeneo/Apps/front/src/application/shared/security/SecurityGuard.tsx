import React, {PropsWithChildren, ReactNode, FC} from 'react';
import {useSecurity} from './use-security';

type Props = PropsWithChildren<{
    acl: string;
    fallback?: ReactNode;
}>;

export const SecurityGuard: FC<Props> = ({acl, fallback, children}: Props) => {
    const security = useSecurity();

    if (true === security.isGranted(acl)) {
        return <>{children}</>;
    }

    return <>{fallback}</>;
};
