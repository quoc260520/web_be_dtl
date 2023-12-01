FROM nginx:alpine3.17

RUN ln -sf /dev/stdout /var/log/nginx/access.log && \
    ln -sf /dev/stderr /var/log/nginx/error.log && \
    rm -rf /etc/nginx/conf.d/*

# install openssl
RUN apk add --update openssl && \
    rm -rf /var/cache/apk/*

EXPOSE 80

# put nginx config
COPY ./docker/nginx/conf.d/ /etc/nginx/conf.d/
