# work
TZ work5
Протестировать тут https://rrdev.ru/


    1. PHP

Создать страницу с авторизацией пользователя: логин и пароль и реализовать в ней:
    • возможность регистрации пользователя (email, логин, пароль, ФИО),
    • при входе в "личный кабинет" возможность сменить пароль и ФИО. 
    • использовать "чистый" PHP 5.6 и выше (без фреймворков) и MySQL 5.5 и выше, дизайн не важен, верстка тоже простая. Наворотов не нужно, хотим посмотреть просто Ваш код.

    2. SQL

Есть 2 таблицы 

таблица пользователей:
users 
----------
`id` int(11)
`email` varchar(55)
`login` varchar(55)

и таблица заказов 
orders
--------
`id` int(11)
`user_id` int(11)
`price` int(11)

Необходимо :
составить запрос, который выведет список email'лов встречающихся более чем у одного пользователя
```
SELECT
	`email`,
	COUNT(`email`) AS `count`
FROM
	`user`
GROUP BY
	`email`
HAVING 
	`count` > 1
```

вывести список логинов пользователей, которые не сделали ни одного заказа

```
SELECT 
	`user`.`login` 
FROM 
	`user` 
WHERE 
	id 
NOT IN 
	(
	SELECT 
		`orders`.`user_id` 
	FROM 	
		`orders` 
	INNER JOIN 
		`user` 
	ON 
		`orders`.`user_id`=`user`.`id`
	)
```
или так `select u.login from user u left join orders o on u.id=o.user_id where o.id is null`

вывести список логинов пользователей которые сделали более двух заказов
```
SELECT 
	`user`.`login`
FROM 
	`orders` 
INNER JOIN 
	`user` 
ON 
	`user`.`id`=`orders`.`user_id`
WHERE 
	orders.price>2
GROUP BY 
	`user`.`id`

```


