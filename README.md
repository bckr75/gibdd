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
После этого
```
composer install
```
## Использование
### Конструктор
Класс инициализируется с опциональным массивом параметров, например(все доступные параметры):  
```
[ 
  'timeout' => 30,                                                            //таймаут соединения
  'useragent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 ' .
            '(KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',         //строка user-agent
  'host' => 'http://check.gibdd.ru',                                          //хост из URI POST-запроса
  'check_path' => '/proxy/check/auto/',                                       //остальная часть URI POST-запроса
  'captcha_path' => '/proxy/captcha.jpg',                                     //часть URI GET-запроса изображения капчи
  'referrer' => 'http://check.gibdd.ru/proxy/captcha.jpg'                     //реферрер
  'proxy' => [
    'address' => '127.0.0.1:80', //ip:порт
    'userpass' => 'root:12345' //юзернейм:пароль
  ]
]
```
__При соединении можно использовать прокси(если не используете, просто уберите 'proxy' из массива).__

__Вы можете указать массив прокси c элементами вида__
```php 
  'proxy' => [
    ['address' => 'ip1:port1', 'userpass' => 'username1:password1'],
    ['address' => 'ip2:port2', 'userpass' => 'username2:password2'],
    ...
  ]
```
__Для того, чтобы иметь возможность каждый раз устанавливать новый прокси при запросе в цикле, не создавая при этом новый класс.__
### Капча
Перед каждой проверкой нужно получить капчу. 
Для этого необходимо вызвать функцию getCaptchaValue с опциональным массивом параметров:
```
[ 
  'setCookie' => true, 
  'base64' => true //возврат капчи, закодированной в base64 и готовой к вставке в html, как аттрибут src элемента img
]
```
__Обратите внимание на опцию setCookie, так как по умолчанию куки устанавливается внутри curl 
данного экземпляра класса, так что если вы создаёте новый класс каждый раз, то вам нужно устанавливать куки в браузере, 
за что эта опция и отвечает.__

### Запрос к ГИБДД
Четыре функции в классе, __tryGetHistory__, __tryGetDtp__, __tryGetIsWanted__ и __tryGetRestrictions__ 
отвечают за четыре соответствующие проверки в ГИБДД.
Все они должны вызываться с обязательными параметрами __VIN__ и __captcha__.

## [Массивы для сопоставления выходных данных](https://github.com/bckr75/gibdd/wiki/%D0%9C%D0%B0%D1%81%D1%81%D0%B8%D0%B2%D1%8B-%D0%B4%D0%BB%D1%8F-%D1%81%D0%BE%D0%BF%D0%BE%D1%81%D1%82%D0%B0%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D1%8F(%D1%80%D0%B0%D1%81%D1%88%D0%B8%D1%84%D1%80%D0%BE%D0%B2%D0%BA%D0%B0) "Массивы для сопоставления выходных данных")
