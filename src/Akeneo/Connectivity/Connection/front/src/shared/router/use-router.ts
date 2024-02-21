import {useContext} from 'react';
import {RouterContext} from './router-context';

export const useRouter = () => {
    const {generate} = useContext(RouterContext);

    return generate;
};
