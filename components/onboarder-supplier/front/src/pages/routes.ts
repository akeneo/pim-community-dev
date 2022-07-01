export enum routes {
    setUpPassword = '/set-up-password/:accessToken',
    login = '/login',
    filesDropping = '/',
    resetPassword = '/reset-password',
}

export const publicRoutesRegex = 'set-up-password|login|reset-password';
