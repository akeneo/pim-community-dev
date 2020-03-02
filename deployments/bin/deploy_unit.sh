#!/bin/bash

if [ "$1" == "usage" ]; then
	echo "This script to run terraform helm upgrade with the latest release"
	echo "USAGE : $0 PATH [RELEASE_TAG]"
	echo "  PATH :full path to access to main.tf/values.yaml of the instance"
	echo "  RELEASE_TAG : (optional) the tag of the release (ex: v20200228020157). If not set, the latest is used"
	exit 99
fi

instance_dir_path=$1
if [ -z "$instance_dir_path" ]; then
	echo "ERROR : You must specify the instance directory path from / (where a stored main.tf & values.yaml)."
	exit 1
fi
if [ -z "$2" ]; then 
	# Get latest tag / docker image name
	tag_to_release=$(git fetch origin &> /dev/null && git tag --list | grep -E '^v?[0-9]+$' | sort -r | head -n 1)
else
	# We have a tag in parameters, test if all is good
	custom_tag_to_release=$2
	tag_to_release=$(git fetch origin &> /dev/null && git tag --list | grep ${custom_tag_to_release})
	if [ -z ${tag_to_release} ]; then
		echo "ERROR : Release ${custom_tag_to_release} in parameter does not exist, exiting."
		exit 1
	fi
fi
# Get instance_name from cloud_customers path
instance_name=$(echo ${instance_dir_path}|awk -F'/' '{print $NF}')
echo "** upgrade ${instance_name} env with release tag : ${tag_to_release} **"

# Change TF files with the latest release
sed -i -E 's/v[0-9]{14}/'${tag_to_release}'/g' ${instance_dir_path}/main.tf

# Scale pim-web & pim-daemon to 0
kubectl scale -n ${instance_name} deploy/pim-web deploy/pim-daemon-default --replicas=0

# Upgrade PIM
pushd . > /dev/null
cd ${instance_dir_path}
terraform init -upgrade 
terraform apply
popd > /dev/null

# UpScale pim-web & pim-daemon
kubectl scale -n ${instance_name} deploy/pim-web deploy/pim-daemon-default --replicas=2

