1. Переименовать .env.dist в .env.local
2. Запустить из папки make init
2. В консоли контейнера выполнить `php bin/console doctrine:migrations:migrate` и `php bin/console doctrine:fixtures:load`