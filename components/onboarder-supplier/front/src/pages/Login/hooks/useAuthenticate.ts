import {useMutation} from 'react-query';
import {authenticate} from '../api/authenticate';
import {UnauthorizedError} from '../../../api';
import {useUserContext} from '../../../contexts';
import {useHistory} from 'react-router-dom';
import {routes} from '../../routes';

const useAuthenticate = () => {
    const mutation = useMutation(authenticate);
    const {updateUser} = useUserContext();
    const history = useHistory();

    const login = async (email: string, password: string) => {
        try {
            const response = await mutation.mutateAsync({email, password});
            updateUser({email: response.email});
            history.push(routes.filesDropping);
        } catch (error) {
            if (error instanceof UnauthorizedError) {
                return false;
            }
        }

        return true;
    };

    return {
        login,
    };
};

export {useAuthenticate};
