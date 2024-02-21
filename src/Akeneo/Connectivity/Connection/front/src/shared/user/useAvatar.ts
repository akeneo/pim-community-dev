import {useContext} from 'react';
import {UserContext} from '.';
import {useRouter} from '../router/use-router';

export const useAvatar = () => {
    const user = useContext(UserContext);
    const generateUrl = useRouter();

    const avatar = user.get<{filePath: string | null}>('avatar');
    const firstName = user.get<string>('first_name');
    const lastName = user.get<string>('last_name');

    const imageUrl =
        avatar.filePath !== null
            ? generateUrl('pim_enrich_media_show', {
                  filename: encodeURIComponent(avatar.filePath),
                  filter: 'thumbnail_small',
              })
            : 'bundles/pimui/images/info-user.png';

    return {firstName, lastName, imageUrl};
};
