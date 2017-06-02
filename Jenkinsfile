#!groovy

stage("Build") {
    podTemplate(label: "build", containers: [
        containerTemplate(name: "docker", image: "paulwoelfel/docker-gcloud", ttyEnabled: true, command: 'cat', envVars: [containerEnvVar(key: "DOCKER_API_VERSION", value: "1.23")]),
        containerTemplate(name: "php", alwaysPullImage: true, ttyEnabled: true, command: 'cat', image: "eu.gcr.io/akeneo-ci/php:7.1", envVars: [containerEnvVar(key: "COMPOSER_HOME", value: "/shared/.composer")]),
        containerTemplate(name: "node", ttyEnabled: true, command: 'cat', image: "node:8"),
        containerTemplate(name: "provisioner", ttyEnabled: true, command: 'cat', image: "gentux/kubectl:latest")
    ], volumes: [
        nfsVolume(mountPath: '/shared', serverAddress: '10.3.247.71', serverPath: '/exports', readOnly: false),
        hostPathVolume(hostPath: "/var/run/docker.sock", mountPath: "/var/run/docker.sock")
    ]) {
        node("build") {
            // workaround to use the same path during all the build
            dir('/home/jenkins/pim') {
                checkout scm

                container("php") {
                    sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist --ignore-platform-reqs"
                    sh "app/console assets:install"
                    sh "app/console pim:installer:dump-require-paths"
                }

                container("node") {
                    sh "npm config set cache /shared/.npm --global"
                    sh "npm install"
                    sh "npm run webpack"
                }

                container("docker") {
                    sh "docker build -t eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ce ."
                    sh "gcloud docker -- push eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ce"

                    sh "docker build -t eu.gcr.io/akeneo-ci/php:7.1 Dockerfiles/php/7.1"
                    sh "gcloud docker -- push eu.gcr.io/akeneo-ci/php:7.1"
                }
            }

            //container("php") {
            //    sh "rm -rf *"
            //    checkout([$class: "GitSCM",
            //        branches: [[name: "kubernetes-experimentation"]],
            //        userRemoteConfigs: [[credentialsId: "github-credentials", url: "https://github.com/ClementGautier/pim-enterprise-dev.git"]]
            //    ])
            //    sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist --ignore-platform-reqs"
            //    sh "app/console pim:installer:dump-require-paths"
            //}

            //container("node") {
            //    sh "npm install"
            //    sh "npm run webpack"
            //}

            //container("docker") {
            //    sh "docker build -t eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ee ."
            //    sh "gcloud docker -- push eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ee"
            //}
        }
    }
}

stage("Test") {
    parallel(
        "phpunit": {
            withPhp({
                sh "cd /home/jenkins/pim && bin/phpunit -c app/phpunit.xml.dist --testsuite PIM_Unit_Test"
            })
        },
        "phpspec": {
            withPhp({
                sh "cd /home/jenkins/pim && chown -R php ."
                sh "cd /home/jenkins/pim && su php -c 'bin/phpspec run --format=dot'"
            })
        },
        "php-cs-fixer": {
            withPhp({
                sh "cd /home/jenkins/pim && bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php"
            })
        },
        "grunt": {
            withNode({
                sh "cd /home/jenkins/pim && npm run webpack"
                sh "cd /home/jenkins/pim && npm run lint"
                sh "cd /home/jenkins/pim && npm run webpack-jasmine"
            })
        },
        "php-coupling-detector": {
            withPhp({
                sh "cd /home/jenkins/pim && bin/php-coupling-detector detect --config-file=.php_cd.php src"
            })
        }
    )
}

def withPhp(body) {
    podTemplate(label: "php", containers: [
        containerTemplate(name: "php", alwaysPullImage: true, ttyEnabled: true, command: 'cat', image: "eu.gcr.io/akeneo-ci/php:7.1")
    ], annotations: [
        podAnnotation(key: "pod.beta.kubernetes.io/init-containers", value: "[{\"name\": \"pim\", \"image\": \"eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ce\", \"command\": [\"sh\", \"-c\", \"cp -Rp /pim /home/jenkins\"], \"volumeMounts\":[{\"name\":\"workspace-volume\",\"mountPath\":\"/home/jenkins\"}]}]")
    ]) {
        node("php") {
            container("php") {
                body()
            }
        }
    }
}

def withNode(body) {
    podTemplate(label: "node", containers: [
        containerTemplate(name: "node", ttyEnabled: true, command: 'cat', image: "node:8")
    ], annotations: [
        podAnnotation(key: "pod.beta.kubernetes.io/init-containers", value: "[{\"name\": \"pim\", \"image\": \"eu.gcr.io/akeneo-ci/pim-community-dev:pull-request-${env.CHANGE_ID}-build-${env.BUILD_NUMBER}-ce\", \"command\": [\"sh\", \"-c\", \"cp -Rp /pim /home/jenkins\"], \"volumeMounts\":[{\"name\":\"workspace-volume\",\"mountPath\":\"/home/jenkins\"}]}]")
    ]) {
        node("node") {
            container("node") {
                body()
            }
        }
    }
}

//stage("Provision") {
//    podTemplate(label: "provisioner", containers: [
//    ]) {
//        node("provisioner") {
//            container("provisioner") {
//                sh "kubectl apply -f .ci/k8s/"
//            }
//        }
//    }
//}
//
//stage("Push") {
//    podTemplate(label: "push", containers: [
//        containerTemplate(name: "pubsub", ttyEnabled: true, command: 'cat', image: "eu.gcr.io/akeneo-ci/pubsub", envVars: [containerEnvVar(key: "DOCKER_API_VERSION", value: "1.23")])
//    ], volumes: [
//        hostPathVolume(hostPath: "/var/run/docker.sock", mountPath: "/var/run/docker.sock"),
//        hostPathVolume(hostPath: "/usr/bin/docker", mountPath: "/usr/bin/docker")
//    ]) {
//        node("push") {
//            container("pubsub") {
//                sh "create-topic akeneo-ci-topic"
//                sh "push-message akeneo-ci-topic \"docker exec php -i\""
//            }
//        }
//    }
//}
