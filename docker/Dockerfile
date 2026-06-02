FROM ghcr.io/open-webui/open-webui:ollama

COPY docker/entrypoint.sh /app/backend/entrypoint-custom.sh
RUN chmod +x /app/backend/entrypoint-custom.sh

ENV OLLAMA_KEEP_ALIVE=24h

EXPOSE 8080 11434

CMD ["bash", "entrypoint-custom.sh"]
