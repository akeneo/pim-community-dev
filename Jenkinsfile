#!groovy

def storages = ["orm", "odm"]
def features = "features,vendor/akeneo/pim-community-dev/features"
def ceBranch = "1.6.x-dev"
def ceOwner = "akeneo"
def launchUnitTests = "yes"
def launchBehatTests = "yes"

stage("Checkout") {
    milestone 1
    if (env.BRANCH_NAME =~ /^PR-/) {
        userInput = input(message: 'Launch tests?', parameters: [
            choice(choices: 'yes\nno', description: 'Run unit tests', name: 'launchUnitTests'),
            choice(choices: 'yes\nno', description: 'Run functional tests', name: 'launchBehatTests'),
            string(defaultValue: 'odm,orm', description: 'Storage used for the build (comma separated values)', name: 'storages'),
            string(defaultValue: 'features,vendor/akeneo/pim-community-dev/features', description: 'Behat scenarios to build', name: 'features'),
            string(defaultValue: '1.6.x-dev', description: 'Community Edition branch used for the build', name: 'ce_branch'),
            string(defaultValue: 'akeneo', description: 'Owner of the repository on GitHub', name: 'ce_owner')
        ])

        storages = userInput['storages'].tokenize(',')
        ceBranch = userInput['ce_branch']
        ceOwner = userInput['ce_owner']
        features = userInput['features']
        launchUnitTests = userInput['launchUnitTests']
        launchBehatTests = userInput['launchBehatTests']
    }
    milestone 2

    node {
        deleteDir()
        checkout scm
        stash "project_files"
    }

    node('docker') {
        deleteDir()
        docker.image("carcel/php:5.6").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
            unstash "project_files"
            if ('akeneo' != ceOwner) {
                sh "composer config repositories.pim-community-dev vcs \"https://github.com/${ceOwner}/pim-community-dev.git\""
            }

            sh "composer require --no-update \"akeneo/pim-community-dev\":\"${ceBranch}\""
            sh "php -d memory_limit=-1 /usr/local/bin/composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
            sh "app/console oro:requirejs:generate-config"
            sh "app/console assets:install"
            stash "project_files_full"
        }
        deleteDir()
    }
}

if (launchUnitTests.equals("yes")) {
    stage("Unit tests") {
        def tasks = [:]

        tasks["phpspec-5.6"] = {runPhpSpecTest("5.6")}
        tasks["phpspec-7.0"] = {runPhpSpecTest("7.0")}
        tasks["php-cs-fixer-5.6"] = {runPhpCsFixerTest("5.6")}
        tasks["php-cs-fixer-7.0"] = {runPhpCsFixerTest("7.0")}
        tasks["grunt"] = {runGruntTest()}

        parallel tasks
    }
}

if (launchBehatTests.equals("yes")) {
    stage("Functional tests") {
        def tasks = [:]

        if (storages.contains('odm')) {tasks["behat-odm"] = {runBehatTest("odm", features)}}
        if (storages.contains('orm')) {tasks["behat-orm"] = {runBehatTest("orm", features)}}

        parallel tasks
    }
}

def runGruntTest() {
    node('docker') {
        deleteDir()
        try {
            docker.image('digitallyseamless/nodejs-bower-grunt').inside("") {
                unstash "project_files_full"
                sh "npm install"
                sh "grunt travis"
            }
        } finally {
            deleteDir()
        }
    }
}

def runPhpSpecTest(version) {
    node('docker') {
        deleteDir()
        try {
            docker.image("carcel/php:${version}").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "project_files"

                if (phpVersion != "5.6") {
                    sh "composer require --no-update alcaeus/mongo-php-adapter"
                }

                sh "php -d memory_limit=-1 /usr/local/bin/composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "touch app/config/parameters_test.yml"
                sh "mkdir -p app/build/logs/"
                sh "./bin/phpspec run --no-interaction --format=junit > app/build/logs/phpspec.xml"
            }
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${version}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            deleteDir()
        }
    }
}

def runPhpCsFixerTest(version) {
    node('docker') {
        deleteDir()
        try {
            docker.image("carcel/php:${version}").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "project_files"

                if (phpVersion != "5.6") {
                    sh "composer require --no-update alcaeus/mongo-php-adapter"
                }

                sh "php -d memory_limit=-1 /usr/local/bin/composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "composer global require friendsofphp/php-cs-fixer ^2.0"
                sh "touch app/config/parameters_test.yml"
                sh "mkdir -p app/build/logs/"
                sh "/home/akeneo/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix --diff --format=junit --config=.php_cs.dist > app/build/logs/phpcs.xml"
            }
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${version}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            deleteDir()
        }
    }
}

def runBehatTest(storage, features) {
    node() {
        dir("behat-${storage}") {
            deleteDir()
            unstash "project_files_full"
            tags = "~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app&&~ce"

            // Create mysql hostname (MySQL docker container name)
            mysqlHostName = "mysql_${env.JOB_NAME}_${env.BUILD_NUMBER}_behat-${storage}".replaceAll( '/', '_' )

            // Configure the PIM
            sh "cp app/config/parameters.yml.dist app/config/parameters_test.yml"
            sh "sed -i \"s#database_host: .*#database_host: ${mysqlHostName}#g\" app/config/parameters_test.yml"
            sh "printf \"    installer_data: 'PimEnterpriseInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"

            // Activate MongoDB if needed
            if ('odm' == storage) {
               sh "sed -i \"s@// new Doctrine@new Doctrine@g\" app/AppKernel.php"
               sh "sed -i \"s@# mongodb_database: .*@mongodb_database: akeneo_pim@g\" app/config/pim_parameters.yml"
               sh "sed -i \"s@# mongodb_server: .*@mongodb_server: 'mongodb://mongodb:27017'@g\" app/config/pim_parameters.yml"
               sh "printf \"    pim_catalog_product_storage_driver: doctrine/mongodb-odm\n\" >> app/config/parameters_test.yml"
            }

            sh "mkdir -p app/build/logs/behat app/build/logs/consumer app/build/screenshots"
            sh "cp behat.ci.yml behat.yml"

            try {
                sh "php /var/lib/distributed-ci/dci-master/bin/build ${env.WORKSPACE}/behat-${storage} ${env.BUILD_NUMBER} ${storage} ${features} ${env.JOB_NAME} 5 5.6 5.5 \"${tags}\" \"behat-${storage}\" --exit_on_failure"
            } finally {
                sh "sed -i \"s/ name=\\\"/ name=\\\"[${storage}] /\" app/build/logs/behat/*.xml"
                junit 'app/build/logs/behat/*.xml'
                archiveArtifacts allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png'
                deleteDir()
            }
        }
    }
}
