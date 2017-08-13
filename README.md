# Простой парсер сайта [ГИБДД](http://gibdd.ru/ "Гибдд")
Предназначен для парсинга ГИБДД. 
Доступны все 4 вида проверки, перед проверкой необходимо запросить капчу(входит в комплект).
## Установка
### Через composer
`composer require bckr75/gibdd`
### Через composer.json
```
require: { 
  "bckr75/gibdd": "^1.0.0"
} 
```
## Использование
### Конструктор
Класс инициализируется с опциональным массивом параметров, например:  
```
[ 
  'timeout' => 30, //таймаут соединения
  'proxy' => [
    'address' => '127.0.0.1:80', //ip:порт
    'userpass' => 'root:12345' //юзернейм:пароль
  ]
]
```
Да, при соединении можно использовать прокси.
### Капча
Перед каждой проверкой нужно получить капчу. 
Для этого необходимо вызвать функцию getCaptchaValue с опциональным массивом параметров:
```
[ 
  'setCookie' => true, 
  'base64' => true //возврат капчи, закодированной в base64 и готовой к вставке в html, как тэг src элемента img
]
```
__Обратите внимание на опцию setCookie, так как по умолчанию куки устанавливается внутри curl 
данного экземпляра класса, так что если вы создаёте новый класс каждый раз, то вам нужно устанавливать куки в браузере, 
за что эта опция и отвечает__

### Запрос к ГИБДД
Четыре функции в классе, __tryGetHistory__, __tryGetDtp__, __tryGetIsWanted__ и __tryGetRestrictions__ 
отвечают за четыре соответствующие проверки в ГИБДД.
Все они должны вызываться с обязательными параметрами __VIN__ и __captcha__.
