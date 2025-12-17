# Jenkins CI/CD Setup Guide

This directory contains Jenkins configuration for automated CI/CD pipelines.

## Quick Start

### 1. Start Jenkins
```bash
# From the project root
docker-compose up -d jenkins
```

### 2. Get Initial Admin Password
```bash
docker exec ticketing_jenkins cat /var/jenkins_home/secrets/initialAdminPassword
```

### 3. Access Jenkins
Open your browser and navigate to:
```
http://localhost:8080
```

### 4. Initial Setup
1. Enter the initial admin password from step 2
2. Click "Install suggested plugins"
3. Create your admin user
4. Click "Save and Continue" and "Start using Jenkins"

## Creating Pipeline Jobs

### Backend Pipeline

1. Click "New Item"
2. Enter name: `Ticketing-Backend`
3. Select "Pipeline" and click OK
4. Under "Pipeline" section:
   - Definition: "Pipeline script from SCM"
   - SCM: Git (if using repository) or "Pipeline script"
   - If using local files, select "Pipeline script" and paste content from `Jenkinsfile-backend`
   - Or set Script Path: `Jenkinsfile-backend`
5. Click "Save"

### Frontend Pipeline

1. Click "New Item"
2. Enter name: `Ticketing-Frontend`  
3. Select "Pipeline" and click OK
4. Under "Pipeline" section:
   - Definition: "Pipeline script from SCM"
   - SCM: Git (if using repository) or "Pipeline script"
   - If using local files, select "Pipeline script" and paste content from `Jenkinsfile-frontend`
   - Or set Script Path: `Jenkinsfile-frontend`
5. Click "Save"

## Running Builds

### Manual Build
1. Go to Pipeline job (e.g., "Ticketing-Backend")
2. Click "Build Now"
3. Monitor progress in "Build History"

### Automatic Builds (Optional)
Configure webhooks in your Git repository to trigger builds on push.

## Troubleshooting

### Cannot connect to Docker daemon
If you see Docker connection errors, ensure:
- Docker socket is properly mounted
- Jenkins has permission to access Docker socket
- Run: `docker exec -it ticketing_jenkins ls -la /var/run/docker.sock`

### Build fails on dependency installation
- Check network connectivity inside Jenkins container
- Verify package managers (npm, composer) are installed
- Check logs: `docker logs ticketing_jenkins`

### Container deployment fails
- Ensure containers aren't already running: `docker ps`
- Check network exists: `docker network ls | grep ticketing`
- Verify volumes are mounted correctly

## Maintenance

### View Jenkins Logs
```bash
docker logs -f ticketing_jenkins
```

### Restart Jenkins
```bash
docker-compose restart jenkins
```

### Backup Jenkins Data
```bash
docker run --rm -v ticketing_jenkins_home:/data -v $(pwd):/backup alpine tar czf /backup/jenkins-backup.tar.gz /data
```

### Restore Jenkins Data
```bash
docker run --rm -v ticketing_jenkins_home:/data -v $(pwd):/backup alpine tar xzf /backup/jenkins-backup.tar.gz -C /
```

## Security Notes

⚠️ **Important**: This setup runs Jenkins as root for Docker socket access. For production:
- Create a dedicated Jenkins user
- Add user to docker group
- Use proper authentication and authorization
- Secure Jenkins behind reverse proxy with HTTPS
- Enable CSRF protection
- Configure proper firewall rules
