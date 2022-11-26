# EBAY CRAWL
- env configuration
  + DATABASE:
      - DB_HOST=127.0.0.1
      - DB_PORT=3306
      - DB_DATABASE=ebay_crawl
      - DB_USERNAME=root
      - DB_PASSWORD=password
   + CRWAL CONFIG
      - EBAY_CRAWL_URL=https://www.ebay-kleinanzeigen.de/s-autos/anbieter:privat/anzeige:angebote/preis:200:5000/seite:__CURRENT_PAGE__/auto/k0c216
      - EBAY_URL=https://www.ebay-kleinanzeigen.de
      - EBAY_DAILY_CRAWL=01:08
      - PROXY=192.99.34.64
      - HOST=1337
      - CUSTOM_PROXY=true
   + CACHE:
      - CACHE_DRIVER=file
   + QUEUE:
      - QUEUE_CONNECTION=database
  
- command to use:
   - composer install
   - npm install
   - npm run watch || npm run build
   - php artisan migrate --seed
   - php artisan serve
   - php artisan queue:work --timeout=0
   - php artisan schedule:work
   
- clear cache when pull code:
   - php artisan optimize:clear
   - php artisan queue:clear
   - php artisan queue:restart
   - php artisan queue:work

- account login:
 = emai: admin@admin.com
 - password: secret

- command crawl:
 - php artisan command:daily-ebay-crawl
