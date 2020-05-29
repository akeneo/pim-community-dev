import {useEffect} from 'react';
import {useDispatch, useSelector} from 'react-redux';
import {AttributeOptionsState} from '../store/store';
import {initializeLocalesAction} from '../reducers';

const useLocales = () => {
    const dispatchAction = useDispatch();

    useEffect(() => {
        dispatchAction(initializeLocalesAction(
            [{"code":"de_DE","label":"German (Germany)"},{"code":"en_US","label":"English (United States)"},{"code":"fr_FR","label":"French (France)"}]
        ));
    }, []);

    return useSelector((state: AttributeOptionsState) => state.locales);
};

export default useLocales;
