import { FC } from 'react';
import { IllustrationProps } from './header';
declare type PageHeaderProps = {
    showPlaceholder?: boolean;
};
interface PageHeaderInterface extends FC<PageHeaderProps> {
    Actions: FC;
    Breadcrumb: FC;
    Illustration: FC<IllustrationProps>;
    UserActions: FC;
    Title: FC;
    State: FC;
}
declare const PageHeader: PageHeaderInterface;
export { PageHeader };
