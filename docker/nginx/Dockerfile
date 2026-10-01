FROM nginx:1.29-alpine

RUN apk add --no-cache openssl \
    && mkdir -p /etc/ssl/private /etc/ssl/certs \
    && openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
        -keyout /etc/ssl/private/localhost.key \
        -out /etc/ssl/certs/localhost.crt \
        -subj "/CN=localhost"

COPY ./conf.d/* /etc/nginx/conf.d/
COPY ./snippets/* /etc/nginx/snippets/

WORKDIR /var/www/portfolio/october-sandbox

EXPOSE 80 443
