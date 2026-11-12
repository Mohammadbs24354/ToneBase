FROM node:22-alpine AS admin-build

WORKDIR /app

COPY admin/package.json ./
RUN npm install --no-audit --no-fund

COPY admin/ ./
RUN npm run build

FROM nginx:1.27-alpine

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY public/ /var/www/html/public/
COPY --from=admin-build /app/dist/ /var/www/html/public/admin/

EXPOSE 80
