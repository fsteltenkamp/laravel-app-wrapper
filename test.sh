#!/bin/bash

echo "================================================"
echo "          Removing old stuff"
sudo docker compose down
COMPOSE_PROJECT_NAME=$(sudo docker compose config | head -n 1)
sudo docker container rm ${COMPOSE_PROJECT_NAME}-queueworker-1
sudo docker container rm ${COMPOSE_PROJECT_NAME}-app-1
sudo docker container rm ${COMPOSE_PROJECT_NAME}-redis-1
sudo docker container rm ${COMPOSE_PROJECT_NAME}-db-1
sudo docker.container rm ${COMPOSE_PROJECT_NAME}-search-1
sudo docker volume rm ${COMPOSE_PROJECT_NAME}_db_data
sudo docker volume rm ${COMPOSE_PROJECT_NAME}_persistent_data
sudo docker volume rm ${COMPOSE_PROJECT_NAME}_search_data
sudo docker image prune -f
sudo docker volume prune -f
sudo docker container prune -f
sudo docker builder prune -f
echo "==============================================="
echo "          Starting containers"
sudo docker compose up