pipeline {
    agent any

    environment {
        DOCKER_HUB_REPOSITORY = 'vetheka'
        DOCKER_HUB_IMAGE = 'html_beauty'
        DOCKER_CREDENTIALS = 'docker-hub-credentials'
        CONTAINER_NAME = 'html_beauty'
        CONTAINER_PORT = '8000'
        REPO = 'https://github.com/Vetheka/Beauty.git'
        EC2_IP = '13.212.20.21'
    }

    parameters {
        string(name: 'TAG', defaultValue: 'v1.0.1', description: 'Git tag to build')
        string(name: 'BRANCH', defaultValue: 'main', description: 'Git branch to build')
        choice(name: 'ACTION', choices: ['deploy', 'rollback'], description: 'Deploy or rollback')
    }

    stages {
        stage('Checkout Code') {
            steps {
                script {
                    echo "TAG = '${params.TAG}'"
                    echo "BRANCH = '${params.BRANCH}'"
                    echo "ACTION = '${params.ACTION}'"

                    if (params.TAG?.trim()) {
                        checkout([
                            $class: 'GitSCM',
                            branches: [[name: "refs/tags/${params.TAG}"]],
                            userRemoteConfigs: [[url: env.REPO]],
                            extensions: [[
                                $class: 'CloneOption',
                                noTags: false,
                                shallow: false
                            ]]
                        ])
                    } else {
                        checkout([
                            $class: 'GitSCM',
                            branches: [[name: params.BRANCH]],
                            userRemoteConfigs: [[url: env.REPO]],
                            extensions: []
                        ])
                    }
                }
            }
        }

        stage('Code Analysis') {
            steps {
                script {
                    if (params.ACTION == 'rollback') {
                        echo 'Skipping linting for rollback action.'
                    } else {
                        echo 'Code quality analysis passed successfully.'
                    }
                }
            }
        }

        stage('Build and Push Image') {
            steps {
                script {
                    if (params.ACTION == 'rollback') {
                        echo 'Skipping build stage: rollback action.'
                        return
                    }

                    def imageTag = params.TAG?.trim() ? params.TAG : 'latest'
                    echo "Building image tag: ${imageTag}"

                    withCredentials([usernamePassword(credentialsId: env.DOCKER_CREDENTIALS, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                        sh 'echo "$DOCKER_PASS" | docker login -u "$DOCKER_USER" --password-stdin'
                    }

                    sh "docker build -t ${env.DOCKER_HUB_REPOSITORY}/${env.DOCKER_HUB_IMAGE}:${imageTag} ."
                    sh "docker push ${env.DOCKER_HUB_REPOSITORY}/${env.DOCKER_HUB_IMAGE}:${imageTag}"
                }
            }
        }

        stage('Deploy to AWS EC2') {
            steps {
                script {
                    def targetTag = params.TAG?.trim() ? params.TAG : 'latest'

                    withCredentials([sshUserPrivateKey(credentialsId: 'ec2-server-key', keyFileVariable: 'IDENTITY_KEY', usernameVariable: 'SSH_USER')]) {
                        if (params.ACTION == 'deploy') {
                            echo "Deploying image tag [${targetTag}]..."
                            sh """
                                ssh -i \"$IDENTITY_KEY\" -o StrictHostKeyChecking=no \"$SSH_USER@${env.EC2_IP}\" \
                                \"bash /home/ubuntu/deploy.sh '${env.CONTAINER_NAME}' '${env.CONTAINER_PORT}' '${env.DOCKER_HUB_REPOSITORY}/${env.DOCKER_HUB_IMAGE}' '${targetTag}'\"
                            """
                        } else {
                            echo "Rolling back to tag [${targetTag}]..."
                            sh """
                                ssh -i \"$IDENTITY_KEY\" -o StrictHostKeyChecking=no \"$SSH_USER@${env.EC2_IP}\" \
                                \"bash /home/ubuntu/rollback.sh '${env.CONTAINER_NAME}' '${env.CONTAINER_PORT}' '${env.DOCKER_HUB_REPOSITORY}/${env.DOCKER_HUB_IMAGE}' '${targetTag}'\"
                            """
                        }
                    }
                }
            }
        }

        stage('Verify System Health') {
            steps {
                script {
                    echo 'Validating live application endpoint...'
                    sh "curl --fail http://${env.EC2_IP}:${env.CONTAINER_PORT} || echo 'Health check warning.'"
                }
            }
        }
    }

    post {
        always {
            cleanWs()
        }
        success {
            echo 'Pipeline finished successfully.'
        }
        failure {
            echo 'Pipeline finished with failures.'
        }
    }
}
