import {useEffect} from 'react';
import {useDispatch, useSelector} from 'react-redux';
import {AttributeOptionsState} from '../store/store';
import {initializeAttributeOptionsAction} from '../reducers';

const useAttributeOptions = () => {
    const dispatchAction = useDispatch();

    useEffect(() => {
        dispatchAction(initializeAttributeOptionsAction(
            [{"id":85,"code":"black","optionValues":{"de_DE":{"id":251,"locale":"de_DE","value":"Black"},"en_US":{"id":252,"locale":"en_US","value":"Black"},"fr_FR":{"id":253,"locale":"fr_FR","value":"Noir"}}},{"id":86,"code":"blue","optionValues":{"de_DE":{"id":254,"locale":"de_DE","value":"Blue"},"en_US":{"id":255,"locale":"en_US","value":"Blue"},"fr_FR":{"id":256,"locale":"fr_FR","value":"Bleu"}}},{"id":87,"code":"brown","optionValues":{"de_DE":{"id":257,"locale":"de_DE","value":"Brown"},"en_US":{"id":258,"locale":"en_US","value":"Brown"},"fr_FR":{"id":259,"locale":"fr_FR","value":"Marron"}}},{"id":88,"code":"green","optionValues":{"de_DE":{"id":260,"locale":"de_DE","value":"Green"},"en_US":{"id":261,"locale":"en_US","value":"Green"},"fr_FR":{"id":262,"locale":"fr_FR","value":"Vert"}}},{"id":89,"code":"grey","optionValues":{"de_DE":{"id":263,"locale":"de_DE","value":"Grey"},"en_US":{"id":264,"locale":"en_US","value":"Grey"},"fr_FR":{"id":265,"locale":"fr_FR","value":"Gris"}}},{"id":90,"code":"pink","optionValues":{"de_DE":{"id":266,"locale":"de_DE","value":"Pink"},"en_US":{"id":267,"locale":"en_US","value":"Pink"},"fr_FR":{"id":268,"locale":"fr_FR","value":"Rose"}}},{"id":91,"code":"orange","optionValues":{"de_DE":{"id":269,"locale":"de_DE","value":"Orange"},"en_US":{"id":270,"locale":"en_US","value":"Orange"},"fr_FR":{"id":271,"locale":"fr_FR","value":"Orange"}}},{"id":92,"code":"red","optionValues":{"de_DE":{"id":272,"locale":"de_DE","value":"Red"},"en_US":{"id":273,"locale":"en_US","value":"Red"},"fr_FR":{"id":274,"locale":"fr_FR","value":"Rouge"}}},{"id":93,"code":"yellow","optionValues":{"de_DE":{"id":275,"locale":"de_DE","value":"Yellow"},"en_US":{"id":276,"locale":"en_US","value":"Yellow"},"fr_FR":{"id":277,"locale":"fr_FR","value":"Jaune"}}},{"id":94,"code":"white","optionValues":{"de_DE":{"id":278,"locale":"de_DE","value":"White"},"en_US":{"id":279,"locale":"en_US","value":"White"},"fr_FR":{"id":280,"locale":"fr_FR","value":"Blanc"}}}]
        ));
    }, []);

    return useSelector((state: AttributeOptionsState) => state.attributeOptions);
};

export default useAttributeOptions;
