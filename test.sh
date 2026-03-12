#!/bin/bash

BASE_TAG="ghcr.io/CHANGEME/CHANGEME"
APP_IMAGE_NAME="$BASE_TAG/app:testing"
WORKER_IMAGE_NAME="$BASE_TAG/worker:testing"
SCHEDULER_IMAGE_NAME="$BASE_TAG/scheduler:testing"

echo "================================================"
echo "          Removing old containers and images"
docker compose down
COMPOSE_PROJECT_NAME=$(docker compose config | head -n 1)
docker container rm ${COMPOSE_PROJECT_NAME}-queueworker-1
docker container rm ${COMPOSE_PROJECT_NAME}-app-1
docker container rm ${COMPOSE_PROJECT_NAME}-redis-1
docker container rm ${COMPOSE_PROJECT_NAME}-db-1
docker container rm ${COMPOSE_PROJECT_NAME}-search-1

read -p "Delete volumes? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    docker volume rm ${COMPOSE_PROJECT_NAME}_db_data
    docker volume rm ${COMPOSE_PROJECT_NAME}_persistent_data
    docker volume rm ${COMPOSE_PROJECT_NAME}_persistent_data_worker
    docker volume rm ${COMPOSE_PROJECT_NAME}_search_data
fi

docker image rm ${APP_IMAGE_NAME} -f
docker image rm ${WORKER_IMAGE_NAME} -f
docker image rm ${SCHEDULER_IMAGE_NAME} -f
docker image prune -f
docker volume prune -f
docker container prune -f
docker builder prune -f
echo "==============================================="
echo "          Building Containers"
docker build --target app -t $APP_IMAGE_NAME app/
docker build --target worker -t $WORKER_IMAGE_NAME  app/
docker build --target scheduler -t $SCHEDULER_IMAGE_NAME  app/
echo "==============================================="
echo "          Starting Containers"
docker compose up

read -p "Upload containers? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "==============================================="
    echo "          Uploading Containers"
    APP_PUSH_OUTPUT=$(docker push $APP_IMAGE_NAME)
    WORKER_PUSH_OUTPUT=$(docker push $WORKER_IMAGE_NAME)
    SCHEDULER_PUSH_OUTPUT=$(docker push $SCHEDULER_IMAGE_NAME)
    # Extract the SHA256 digests
    echo "==============================================="
    APP_SHA=$(echo "$APP_PUSH_OUTPUT" | grep "digest:" | grep -oP 'sha256:[a-f0-9]{64}')
    WORKER_SHA=$(echo "$WORKER_PUSH_OUTPUT" | grep "digest:" | grep -oP 'sha256:[a-f0-9]{64}')
    SCHEDULER_SHA=$(echo "$SCHEDULER_PUSH_OUTPUT" | grep "digest:" | grep -oP 'sha256:[a-f0-9]{64}')
    echo "App Digest......: $APP_SHA"
    echo "Worker Digest...: $WORKER_SHA"
    echo "Scheduler Digest: $SCHEDULER_SHA"
    echo "==============================================="
else
    echo "Containers not uploaded."
fi