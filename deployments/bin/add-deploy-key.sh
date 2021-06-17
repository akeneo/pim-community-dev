#!/usr/bin/env bash

set -eu
cat << EOF >> ~/.ssh/config
Host ${REPO_TO_ADD}
  HostName github.com
  IdentitiesOnly yes
  IdentityFile ${KEY_TO_ADD}
  UserKnownHostsFile /root/.ssh/known_hosts
EOF
