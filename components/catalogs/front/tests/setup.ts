// Autoload the extend expect
import '@testing-library/jest-dom';
import {useTranslate} from '@akeneo-pim-community/shared';

(useTranslate as jest.Mock).mockImplementation(() => (key: string) => key);
