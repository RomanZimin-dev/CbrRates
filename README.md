# Проект с использованием Docker, RabbitMQ и Memcache

## Описание

Этот проект включает три Docker-контейнера:
- **web**: контейнер с веб-сервером Apache и PHP 8.1
- **rabbitmq**: контейнер с RabbitMQ для очередей сообщений
- **memcache**: контейнер с Memcached для кэширования данных

## Требования

- [Docker](https://docs.docker.com/engine/install/)
- [Docker Compose](https://docs.docker.com/compose/install/)
- [Composer](https://getcomposer.org/) для управления зависимостями PHP
  
Команды для установки данных пакетов для Centos 9 приведены в конце README.md, для других ОС восьпользуйтесь
ссылками на документацию каждого пакета (приведены выше).

## Установка

1. Клонируйте репозиторий:

    ```sh
    git clone https://github.com/RomanZimin-dev/CbrRates.git
    cd CbrRates
    ```
2. Запустите Docker Compose для сборки и запуска контейнеров:

    ```sh
    docker-compose up -d
    ```
    При повторной сборке рекомендуется собирать со сбросом кэша

    ```sh
    docker-compose down --rmi all
    docker-compose build --no-cache
    docker-compose up -d
    ```
3.  **Настройте зависимости PHP с Composer:**

    Убедитесь, что Composer установлен. Затем выполните команду:

    ```sh
    cd var/www
    composer install
    ```

    Это установит все зависимости, указанные в `composer.json`.
    
4. Войдите в контейнер web для управления приложением
    ```sh
    docker exec -it web bash
    ```
5. Для выполнения задания 
   ```
   На входе: дата, код валюты, код базовой валюты (по умолчанию RUR)
   получать курсы с http://cbr.ru
   на выходе: значение курса и разница с предыдущим торговым днем
   кешировать данные http://cbr.ru
   ``` 
   Необходимо запустить скрипт index.php с 3 параметрами
    1. Дата в формате "d/m/Y"
    2. Код валюты, например USD
    3. Код базовой валюты (необязательный параметр, по умолчанию RUR)
    ```sh
    php index.php 29/07/2024 USD
    ```    
   или
   ```sh
    php index.php 29/07/2024 USD EUR
    ```   
   Скрипт выдаст значение курса на текущую дату и изменение относительно предыдущего торгового дня,
   а так же закеширует значение курса на текущую дату в memcache 
    ```sh
    85.565 (+0.155)
    ```
   Значение времени кеширования в секундах задаётся константой CACHING_TIME_SEC в классе \CbrCurrencyRates\Cache\Cache
   и по умолчанию равно 1 часу (3600 секунд)   
   
   При повторном запросе значения курсов не будут запрошены в cbr, а будут извлечены из memcache
6. Для выполнения задания
   ```
   продемонстрировать навыки работы с брокерами сообщений и
   реализовать сбор данных с cbr за 180 предыдущих дней с
   помощью воркера через консольную команду
   ``` 
   Необходимо запустить скрипт set_history_task.php c 3 параметрами
   1. Количество предыдущих дней, за которое нужно запросить курсы
   2. Код валюты, например USD
   3. Код базовой валюты (необязательный параметр, по умолчанию RUR)
   ```sh
    php set_history_task.php 180 USD
    ```    
   или
   ```sh
    php set_history_task.php 180 USD EUR
    ``` 
7. Для обработки сообщений из очереди rabbitMq, необходимо запустить скрипт workers/rmq_get_history_rates.php
   ```sh
   php workers/rmq_get_history_rates.php
    ```    
   Скрипт выполнит запросы к cbr и закеширует полученные значение, при повторном запросе курсов
через основной скрипт index.php значения закешированных валютных пар будут доставаться из кэша
   
## Примеры команд установки Docker, Docker Compose, Composer для Linux Centos 9
1. Ставим Docker
   ```sh
   sudo yum install -y yum-utils
   sudo yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
   sudo yum install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
   sudo systemctl start docker
    ``` 
2. Ставим Docker Compose
   ```sh
   sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
   sudo chmod +x /usr/local/bin/docker-compose
    ``` 
3. Ставим Composer.
   1. Сначала зависимости
   ```sh
   sudo yum install -y curl php-cli php-mbstring unzip
    ```
   2 Затем сам Composer
   ```sh
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   php composer-setup.php
   php -r "unlink('composer-setup.php');"   
   sudo mv composer.phar /usr/local/bin/composer
    ``` 
