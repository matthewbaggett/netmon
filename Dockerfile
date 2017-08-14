FROM gone/php:cli
ADD .docker/service /etc/service
RUN chmod +x /etc/service/*/run && \
        chmod +x /app/*.sh && \
    apt-get -qq update && \
    apt-get -yq install --no-install-recommends \
        inetutils-ping \
	    python-pip && \
    pip install speedtest-cli && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
