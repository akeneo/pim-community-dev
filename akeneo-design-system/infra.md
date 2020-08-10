# Infrastructure

To reproduce this extract infra, you need:

- One monorepo with a folder named akeneo-design-system
- Another repository to put the dsm

- Update the property "destination-github-username" and "destination-repository-name" from the `.github/workflows/dsm.yml` file with the owner and name of your dsm repository
- Generate a github token from your account
- Add this token to the monorepo as a secret called API_TOKEN_GITHUB
- Configure the branch on which you want to trigger the deploy in the `.github/workflows/dsm.yml` file.

On each push on the dsm branch, this will launch the tests, generate a lib build and then push everything to the dsm repository
