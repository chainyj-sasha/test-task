<?php

/*
* 1. Вы работаете над проектом умной больницы, где каждый пациентов имеет специальный датчик, который раз в 10 минут передает сведения о пульсе и давлении подопечного.
*  Напишите SQL таблицы для хранения этих данных, учитывая то, что один из самых частых запросов к ней будет: выбор всех подопечных у которых после обеда были превышены нормы пульса и давления.
*/

'CREATE TABLE users (
     id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT
)';

'CREATE TABLE healthIndicators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pressure_max INT,
    pressure_min INT,
    heart_rate INT,
    is_norm BOOLEAN,
    user_id INT,
    created_at TIMESTAMP DEFAULT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);';

/*
 * Пояснение к 1 заданию.
 *
 * Создаю две таблицы, в одной информация о юзерах, во второй записываются их показатели (давление, пульс и т.д.)
 * Перед сохранением данных в БД, например в сервисе, проверять входящие показатели от датчика (норм или не норм) и в общем запросе на сохранение данных в поле "is_norm" сохранять 1 или 0 соответственно.
 * В дальнейшем делать выборку ориентируясь на поля is_norm (превышено или нет) и created_at (значения после обеда).
 * В контексте данного ТЗ это, на мой взгляд, неплохой вариант т.к запрос на выборку будет простым, но применимо к "реальной жизни", следует добавить еще одну таблицу
 * в которой будут храниться данные "нормальных" значений для каждого отдельного юзера (у разных людей разная "нормальная" темперетура, давление и т.д.), такая себе таблица "norms" например.
 * И в дальнейшем при выборке данных делать запрос еще и к этой таблице и проводить сравнение показателей юзеров, "нормальные" и приходящие от датчика.
 * Запрос на добавление такой таблицы внизу.
 */

'CREATE TABLE norms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    pressure_max INT,
    pressure_min INT,
    heart_rate_max INT,
    heart_rate_min INT,
    FOREIGN KEY (user_id) REFERENCES users(id)'
);



/*
 * 2. Напишите PHP скрипт расчёта количества вторников между двумя датами.
*/

 function countTuesdays($firstDate, $secondDate)
 {
     $first = new DateTime($firstDate);
     $second = new DateTime($secondDate);

     $tuesdayCount = 0;

     while ($first <= $second) {
         if ($first->format('N') == 2) {
             $tuesdayCount++;
         }
         $first->modify('+1 day');
     }
     return $tuesdayCount;
 }


/*
   3. Есть таблица, которая хранит сведения о товарах вида:
        CREATE TABLE `products ` (
          `id` int(11) NOT NULL,
          `name` tinytext,
          `price` float(9,2) DEFAULT '0.00',
          `color` tinytext,
          UNIQUE KEY `id` (`id`)
        ) ENGINE=innoDB;

     товаров более 1млн. Различных цветов более 100.

     Перед вами стоит задача, обновить цену в зависимости от цвета товара. Например, товарам с color=red цену уменьшить на 5%, товарам с color=green, увеличить цену на 10% и т.д.
     Напишите PHP + SQL скрипт как это сделать максимально эффективно с точки зрения производительности.
*/

$host = 'localhost';
$db = 'test_db';
$user = 'root';
$pass = 'root';

 try {
     $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);

     $priceRules = [
         'red' => 0.95,
         'green' => 1.10,
     ];

     $pdo->beginTransaction();

     $sql = "UPDATE products SET price = price * :coefficient WHERE color = :color";
     $stm = $pdo->prepare($sql);

     foreach ($priceRules as $color => $coefficient) {
         $stm->bindParam(':coefficient', $coefficient);
         $stm->bindParam(':color', $color);

         $stm->execute();
     }

     $pdo->commit();

     echo "Обновление завершено";

 } catch (PDOException $e) {
     $pdo->rollBack();

     echo "Error: " . $e->getMessage();
 }