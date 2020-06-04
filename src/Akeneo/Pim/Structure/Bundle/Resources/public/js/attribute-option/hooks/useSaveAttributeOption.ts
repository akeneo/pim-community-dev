import {AttributeOption} from '../model';
import {useRouter} from '@akeneo-pim-community/legacy-bridge';
import {useAttributeContext} from '../contexts';

const useSaveAttributeOption = () => {
    const router = useRouter();
    const attribute = useAttributeContext();

    return async (attributeOption: AttributeOption) => {
        await fetch(
            router.generate('pim_enrich_attributeoption_update', {
                attributeId: attribute.attributeId,
                attributeOptionId: attributeOption.id,
            }),
            {
                method: 'PUT',
                headers: [
                    ['Content-type', 'application/json'],
                    ['X-Requested-With', 'XMLHttpRequest'],
                ],
                body: JSON.stringify(attributeOption),
            }
        );
    };
};

export {useSaveAttributeOption};
