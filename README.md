
Очистить DB (те таблицы, которые используются в данном импорте)
> php app.php i-p --clear-all

Запустить импорт
> php app.php i-p

Создать таблицу логов
```SQL
create table log
(
    id                                int auto_increment primary key,
    import_type         varchar(255)                                not null,
    entity_id           int                                 not null,
    msg                 text                                not null,
    date_added          datetime                            not null,
    date_modified       datetime                            not null
)
    charset = utf8;
```

statements:
* все категории активируются (status=1)
* категории без товаров, и у которых вложенные категории так же не имеют товаров, деактивируются (status=0)
* если в базе нет oc_option c option_id=13 (Размер) импорт размеров не производится (просто завершится)
* логи старше месяца удаляются
* в log пишутся найденные в результате проверки входных данных "странности", импорт их наличие не останавливает
* при очистке DB таблица логов так же очищается, но после завершения записывает информация о том какие таблицы были очищены

