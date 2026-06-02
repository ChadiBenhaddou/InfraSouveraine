#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DOCKER_IMAGE="${DOCKER_IMAGE:-infrasouveraine/open-webui:latest}"
RUNPOD_API_KEY="${RUNPOD_API_KEY:-}"
TEMPLATE_NAME="${TEMPLATE_NAME:-InfraSouveraine-Ollama-WebUI}"

if [ -z "$RUNPOD_API_KEY" ]; then
    echo "Error: RUNPOD_API_KEY is not set."
    echo "Usage: RUNPOD_API_KEY=rp_xxx ./scripts/setup-runpod.sh"
    exit 1
fi

echo "=== Step 1: Build Docker image ==="
docker build -t "$DOCKER_IMAGE" -f "$SCRIPT_DIR/docker/Dockerfile" "$SCRIPT_DIR"

echo ""
echo "=== Step 2: Push Docker image ==="
docker push "$DOCKER_IMAGE"

echo ""
echo "=== Step 3: Create RunPod template ==="
RESPONSE=$(curl -s --request POST \
    --header 'content-type: application/json' \
    --url "https://api.runpod.io/graphql?api_key=${RUNPOD_API_KEY}" \
    --data "$(jq -n --arg image "$DOCKER_IMAGE" --arg name "$TEMPLATE_NAME" '{
        "query": "mutation SaveTemplate($input: TemplateInput!) { saveTemplate(input: $input) { id name imageName ports env { key value } } }",
        "variables": {
            "input": {
                "name": $name,
                "imageName": $image,
                "containerDiskInGb": 30,
                "volumeInGb": 50,
                "volumeMountPath": "/root/.ollama",
                "ports": "8080/http",
                "env": [
                    { "key": "OLLAMA_KEEP_ALIVE", "value": "24h" },
                    { "key": "AUTO_PULL_MODEL", "value": "true" }
                ]
            }
        }
    }')")

TEMPLATE_ID=$(echo "$RESPONSE" | jq -r '.data.saveTemplate.id // empty')

if [ -n "$TEMPLATE_ID" ]; then
    echo ""
    echo "=== Template created ==="
    echo "Template ID: $TEMPLATE_ID"
    echo "Name: $TEMPLATE_NAME"
    echo ""
    echo "Add this to your .env file:"
    echo "  RUNPOD_DEFAULT_TEMPLATE_ID=$TEMPLATE_ID"
    echo "  RUNPOD_DEFAULT_IMAGE_NAME=$DOCKER_IMAGE"
else
    echo ""
    echo "Error creating template:"
    echo "$RESPONSE" | jq .
    exit 1
fi
