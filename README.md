# yii2-activity-logger

<table>
    <tr>
        <td>
            <img width="200px" src="https://user-images.githubusercontent.com/675367/33967884-6dc55ca8-e076-11e7-88c5-4ba5d7d69012.png" alt="yii2-activity-logger" />
        </td>
        <td>
            Это расширение поможет вам отслеживать пользовательскую активность на сайте.
            Когда в админке над контентом работает больше двух человек, не всегда понятно кто, когда и зачем сделал изменения в описание статьи, убрал статью из публикации, добавил непонятного пользователя, удалил организацию.
            Для того чтобы была возможность поблагодарить автора за усердную работу и был разработан этот модуль.
        </td>
    </tr>
</table>


[![Total Downloads](https://poser.pugx.org/lav45/yii2-activity-logger/downloads)](https://packagist.org/packages/lav45/yii2-activity-logger)
[![Test Status](https://github.com/lav45/yii2-activity-logger/workflows/test/badge.svg)](https://github.com/lav45/yii2-activity-logger/actions)
[![Code Coverage](https://scrutinizer-ci.com/g/lav45/yii2-activity-logger/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lav45/yii2-activity-logger/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lav45/yii2-activity-logger/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lav45/yii2-activity-logger/)
[![License](https://poser.pugx.org/lav45/yii2-activity-logger/license)](https://github.com/lav45/yii2-activity-logger/blob/master/LICENSE.md)

## Установка расширения

```bash
composer require lav45/yii2-activity-logger --prefer-dist
```

## Подключение и настройка

* [Миграции](doc/migrate.md)
* [Компоненты](doc/component.md)
* [ActiveRecord](doc/ActiveRecord.md)
* [Отображение данных](doc/viewModule.md)
* [MessageData](doc/MessageData.md)
* [Очистки логов](doc/clear.md)
* [Добавление логов](doc/addLogs.md)

## Тестирование

```bash
./build.sh
```

```bash
./composer update --prefer-dist
```

```bash
./composer phpunit
```

## Поддерживаемые версии

| Версия | Версия PHP | Статус       |
|--------|------------|--------------|
| `4.x`  | `>=8.4`    |              |
| `3.x`  | `>=8.1`    | В разработке |
| `2.x`  | `>=7.4`    | Активна      |
| `1.x`  | `>=5.5`    | Завершена    |

## Лицензии

Для получения информации о лицензии проверьте файл [LICENSE.md](LICENSE.md).
