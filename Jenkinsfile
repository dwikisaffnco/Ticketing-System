pipeline {
    agent any
    
    environment {
        // Nama Docker images
        BACKEND_IMAGE = "ticketing-backend"
        FRONTEND_IMAGE = "ticketing-frontend"
        
        // Docker Compose file
        COMPOSE_FILE = "docker-compose.yml"
    }
    
    stages {
        stage('Checkout') {
            steps {
                echo 'Pulling code from GitHub...'
                checkout scm
            }
        }
        
        stage('Build Backend') {
            steps {
                echo 'Building Backend Docker Image...'
                dir('backend') {
                    script {
                        docker.build("${BACKEND_IMAGE}:${BUILD_NUMBER}")
                        docker.build("${BACKEND_IMAGE}:latest")
                    }
                }
            }
        }
        
        stage('Build Frontend') {
            steps {
                echo 'Building Frontend Docker Image...'
                dir('frontend') {
                    script {
                        docker.build("${FRONTEND_IMAGE}:${BUILD_NUMBER}")
                        docker.build("${FRONTEND_IMAGE}:latest")
                    }
                }
            }
        }
        
        stage('Test') {
            steps {
                echo 'Running Tests...'
                // Uncomment dan sesuaikan jika sudah ada tests
                // sh 'cd backend && php artisan test'
                // sh 'cd frontend && npm test'
                echo 'Tests completed (skipped for now)'
            }
        }
        
        stage('Deploy') {
            steps {
                echo 'Deploying with Docker Compose...'
                script {
                    // Stop container lama
                    sh 'docker-compose down || true'
                    
                    // Start container baru
                    sh 'docker-compose up -d'
                    
                    // Check if containers are running
                    sh 'docker-compose ps'
                }
            }
        }
        
        stage('Cleanup') {
            steps {
                echo 'Cleaning up old Docker images...'
                script {
                    // Hapus images lama (keep last 3 builds)
                    sh """
                        docker images ${BACKEND_IMAGE} --format '{{.Tag}}' | \
                        grep -v latest | \
                        tail -n +4 | \
                        xargs -r -I {} docker rmi ${BACKEND_IMAGE}:{} || true
                        
                        docker images ${FRONTEND_IMAGE} --format '{{.Tag}}' | \
                        grep -v latest | \
                        tail -n +4 | \
                        xargs -r -I {} docker rmi ${FRONTEND_IMAGE}:{} || true
                    """
                }
            }
        }
    }
    
    post {
        success {
            echo '✅ Pipeline SUCCESS! Application deployed.'
        }
        failure {
            echo '❌ Pipeline FAILED! Check logs for details.'
        }
        always {
            echo 'Pipeline finished.'
            // Cleanup workspace (optional)
            // cleanWs()
        }
    }
}
