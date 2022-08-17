export enum routes {
    setUpPassword = '/set-up-password/:accessToken',
    login = '/login',
    filesDropping = '/',
    resetPassword = '/reset-password',
    productFileHistory = '/product-file-history',
}

export const publicRoutesRegex = 'set-up-password|login|reset-password';
