export interface Security {
    isGranted: (acl: string) => boolean;
}
