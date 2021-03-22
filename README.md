Please run the following commands before starting the application: 

composer update
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
npm install
yarn encore dev