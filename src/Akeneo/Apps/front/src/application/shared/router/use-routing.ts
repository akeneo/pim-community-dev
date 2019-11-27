import {useContext} from 'react';
import {RouterContext} from './router-context';

export const useRouting = () => {
    const {generate} = useContext(RouterContext);

    return (route: string, parameters?: object) => {
            return generate(route, parameters);
        };
};
