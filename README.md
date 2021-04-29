Symfony project for working with web applications.

Запуск проекта:
- Докер должен быть установлен
- Собрать основной Dockerfile php:
    > sudo docker build --tag psymfonyphp -f ./.docker/config/php/php.dockerfile .
- Собрать docker-compose для разработки:
    > sudo docker-compose -f docker-compose.yml -f docker-compose.dev.yml build
- Запустить:
    > docker-compose -f docker-compose.yml -f docker-compose.dev.yml up

<br /><br />
- Желательно также добавить пользователя в группу docker
    > sudo gpasswd -a $USER docker