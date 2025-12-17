FROM jenkins/jenkins:lts

USER root

# Install Docker CLI
RUN apt-get update && apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release && \
    curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg && \
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/debian $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null && \
    apt-get update && \
    apt-get install -y docker-ce-cli docker-compose-plugin && \
    rm -rf /var/lib/apt/lists/*

# Install Node.js and npm for frontend builds
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# Install PHP and Composer for backend builds
RUN apt-get update && apt-get install -y \
    php php-cli php-mbstring php-xml php-zip unzip git && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    chmod +x /usr/local/bin/composer && \
    rm -rf /var/lib/apt/lists/*

# Switch back to jenkins user
USER jenkins

# Install Jenkins plugins
RUN jenkins-plugin-cli --plugins \
    docker-workflow \
    git \
    credentials-binding \
    workflow-aggregator \
    pipeline-stage-view \
    blueocean

# Skip initial setup wizard (optional)
ENV JAVA_OPTS="-Djenkins.install.runSetupWizard=false"
